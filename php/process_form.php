<?php

// ⚠️ Protección para evitar acceso directo
require_once __DIR__ . '/security/block_direct_access.php';

// 📮 Control de tamaño de payload
require_once __DIR__ . '/security/check_payload_size.php';

// ✅ Validar método y Content-Type
require_once __DIR__ . '/security//validate_content_type.php';
validarMetodoYContentType();

require __DIR__ . '/../vendor/autoload.php';

// Incluir Logger y config_loader
require_once __DIR__ . '/logger/Logger.php';
require_once __DIR__ . '/config/config_loader.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ====================================
// CONFIGURACIÓN INICIAL
// ====================================

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/security/headers.php';

// ====================================
// SISTEMA DE LOGGING
// ====================================

// Inicializar logger
Logger::init(__DIR__ . '/logs');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  Logger::error('general', 'Método HTTP no permitido', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'expected' => 'POST'
  ]);
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Método no permitido']);
  exit;
}

// ====================================
// FUNCIONES DE VALIDACIÓN
// ====================================

function validarCampo($campo, $valor, $tipo = 'texto')
{
  Logger::info('validation', 'Validando campo', [
    'campo' => $campo,
    'tipo' => $tipo,
    'valor_length' => strlen($valor)
  ]);

  $errores = [];

  // Validar campo requerido
  if (empty(trim($valor))) {
    $error = "El campo {$campo} es obligatorio";
    $errores[] = $error;
    Logger::warning('validation', 'Campo requerido vacío', [
      'campo' => $campo,
      'error' => $error
    ]);
    return $errores;
  }

  try {
    switch ($tipo) {
      case 'email':
        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
          $error = "El email no tiene un formato válido";
          $errores[] = $error;
          Logger::warning('validation', 'Email inválido', [
            'campo' => $campo,
            'valor' => $valor,
            'error' => $error
          ]);
        }
        break;

      case 'nombre':
        if (strlen($valor) < 2 || strlen($valor) > 50) {
          $error = "El nombre debe tener entre 2 y 50 caracteres";
          $errores[] = $error;
          Logger::warning('validation', 'Longitud de nombre inválida', [
            'campo' => $campo,
            'longitud' => strlen($valor),
            'error' => $error
          ]);
        }
        if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $valor)) {
          $error = "El nombre solo puede contener letras y espacios";
          $errores[] = $error;
          Logger::warning('validation', 'Caracteres inválidos en nombre', [
            'campo' => $campo,
            'valor' => $valor,
            'error' => $error
          ]);
        }
        break;

      case 'mensaje':
        if (strlen($valor) < 10) {
          $error = "El mensaje debe tener al menos 10 caracteres";
          $errores[] = $error;
          Logger::warning('validation', 'Mensaje muy corto', [
            'campo' => $campo,
            'longitud' => strlen($valor),
            'error' => $error
          ]);
        }
        if (strlen($valor) > 1000) {
          $error = "El mensaje no puede exceder 1000 caracteres";
          $errores[] = $error;
          Logger::warning('validation', 'Mensaje muy largo', [
            'campo' => $campo,
            'longitud' => strlen($valor),
            'error' => $error
          ]);
        }
        break;

      case 'empresa':
        if (strlen($valor) > 100) {
          $error = "El nombre de la empresa no puede exceder 100 caracteres";
          $errores[] = $error;
          Logger::warning('validation', 'Nombre de empresa muy largo', [
            'campo' => $campo,
            'longitud' => strlen($valor),
            'error' => $error
          ]);
        }
        break;
    }

    if (empty($errores)) {
      Logger::success('validation', 'Campo validado correctamente', [
        'campo' => $campo,
        'tipo' => $tipo
      ]);
    }
  } catch (Exception $e) {
    Logger::error('validation', 'Error en validación de campo', [
      'campo' => $campo,
      'tipo' => $tipo,
      'error' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
    ]);
    $errores[] = "Error interno al validar el campo {$campo}";
  }

  return $errores;
}

function limpiarDatos($datos)
{
  Logger::info('sanitization', 'Iniciando limpieza de datos', [
    'campos' => array_keys($datos)
  ]);

  try {
    $datosProcesados = array_map(function ($valor) {
      return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }, $datos);

    Logger::success('sanitization', 'Datos limpiados exitosamente', [
      'campos_procesados' => count($datosProcesados)
    ]);

    return $datosProcesados;
  } catch (Exception $e) {
    Logger::error('sanitization', 'Error al limpiar datos', [
      'error' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
    ]);
    throw $e;
  }
}

function detectarSpam($datos)
{
  Logger::info('spam_detection', 'Iniciando detección de spam');

  try {
    $spam_patterns = [
      'http://',
      'https://',
      'www.',
      'click here',
      'free money',
      'viagra',
      'casino'
    ];

    $contenido = strtolower(implode(' ', $datos));

    foreach ($spam_patterns as $pattern) {
      if (strpos($contenido, $pattern) !== false) {
        Logger::warning('spam_detection', 'Contenido marcado como spam', [
          'pattern_found' => $pattern,
          'contenido_length' => strlen($contenido)
        ]);
        return true;
      }
    }

    Logger::success('spam_detection', 'Contenido aprobado - no es spam');
    return false;
  } catch (Exception $e) {
    Logger::error('spam_detection', 'Error en detección de spam', [
      'error' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
    ]);
    return false; // En caso de error, no bloquear el mensaje
  }
}

// ====================================
// FUNCIONES DE EMAIL
// ====================================

function enviarEmail($datos, $config)
{
  Logger::info('email', 'Iniciando envío de email', [
    'destinatario' => $config['to_email'],
    'remitente' => $datos['email']
  ]);

  $mail = new PHPMailer(true);

  try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['smtp_port'];

    Logger::info('email', 'Configuración SMTP establecida', [
      'host' => $config['smtp_host'],
      'port' => $config['smtp_port'],
      'user' => $config['smtp_user']
    ]);

    // Remitente y destinatario
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['to_email'], $config['to_name']);
    $mail->addReplyTo($datos['email'], $datos['nombre']);

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = "Nuevo contacto desde TechSolutions - " . $datos['nombre'];
    $mail->Body = generarPlantillaEmail($datos);

    $mail->send();

    Logger::success('email', 'Email principal enviado exitosamente', [
      'destinatario' => $config['to_email'],
      'asunto' => $mail->Subject
    ]);

    // Enviar email de confirmación
    $confirmacionEnviada = enviarEmailConfirmacion($datos, $config);

    if ($confirmacionEnviada) {
      Logger::success('email', 'Email de confirmación enviado');
    } else {
      Logger::warning('email', 'Fallo al enviar email de confirmación');
    }

    return true;
  } catch (Exception $e) {
    Logger::error('email', 'Error al enviar email', [
      'error' => $mail->ErrorInfo,
      'exception' => $e->getMessage(),
      'trace' => $e->getTraceAsString(),
      'smtp_host' => $config['smtp_host'],
      'smtp_port' => $config['smtp_port']
    ]);
    return false;
  }
}

function generarPlantillaEmail($datos)
{
  Logger::info('email_template', 'Generando plantilla de email');

  try {
    $servicios = [
      'desarrollo-web' => 'Desarrollo Web',
      'apps-moviles' => 'Apps Móviles',
      'cloud-solutions' => 'Cloud Solutions',
      'consultoria' => 'Consultoría'
    ];

    $servicio_nombre = $servicios[$datos['servicio']] ?? 'No especificado';

    Logger::info('email_template', 'Servicio identificado', [
      'servicio_codigo' => $datos['servicio'],
      'servicio_nombre' => $servicio_nombre
    ]);

    $plantilla = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #2c3e50; margin: 0; }
                .content { margin-bottom: 20px; }
                .field { margin-bottom: 15px; }
                .field strong { color: #34495e; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚀 TechSolutions</h1>
                    <p>Nuevo mensaje de contacto</p>
                </div>
                
                <div class='content'>
                    <div class='field'>
                        <strong>Nombre:</strong> {$datos['nombre']}
                    </div>
                    <div class='field'>
                        <strong>Email:</strong> {$datos['email']}
                    </div>
                    <div class='field'>
                        <strong>Empresa:</strong> " . (!empty($datos['empresa']) ? $datos['empresa'] : 'No especificada') . "
                    </div>
                    <div class='field'>
                        <strong>Servicio de interés:</strong> {$servicio_nombre}
                    </div>
                    <div class='field'>
                        <strong>Mensaje:</strong><br>
                        <p style='background: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 10px 0;'>
                            " . nl2br(htmlspecialchars($datos['mensaje'])) . "
                        </p>
                    </div>
                    <div class='field'>
                        <strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "
                    </div>
                </div>
                
                <div class='footer'>
                    <p>Este mensaje fue enviado desde el formulario de contacto de TechSolutions</p>
                </div>
            </div>
        </body>
        </html>
        ";

    Logger::success('email_template', 'Plantilla de email generada exitosamente');
    return $plantilla;
  } catch (Exception $e) {
    Logger::error('email_template', 'Error al generar plantilla de email', [
      'error' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
    ]);
    throw $e;
  }
}

function enviarEmailConfirmacion($datos, $config)
{
  Logger::info('email_confirmation', 'Enviando email de confirmación', [
    'destinatario' => $datos['email']
  ]);

  $mensaje = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #2c3e50; margin: 0; }
                .content { color: #333; line-height: 1.6; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚀 TechSolutions</h1>
                    <p>¡Gracias por contactarnos!</p>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$datos['nombre']}</strong>,</p>
                    <p>Hemos recibido tu mensaje y te agradecemos por contactarnos. Nuestro equipo revisará tu solicitud y te responderá en un plazo máximo de 24 horas.</p>
                    <p>Mientras tanto, puedes:</p>
                    <ul>
                        <li>Visitar nuestro portafolio en línea</li>
                        <li>Seguirnos en redes sociales</li>
                        <li>Explorar nuestros servicios</li>
                    </ul>
                    <p>Si tienes alguna pregunta urgente, no dudes en llamarnos al <strong>+1 (555) 123-4567</strong></p>
                    <p>¡Esperamos trabajar contigo pronto!</p>
                    <p>Saludos,<br>El equipo de TechSolutions</p>
                    <p style='font-size: 12px; color: #999; margin-top: 20px;'>
                        Si no deseas recibir futuros correos de confirmación, por favor <a href='mailto:{$config['from_email']}?subject=Cancelar%20confirmaciones'>haz clic aquí</a> para solicitarnos la baja.
                    </p>
                </div>
                <div class='footer'>
                    <p>TechSolutions - Transformando ideas en soluciones digitales</p>
                </div>
            </div>
        </body>
        </html>
    ";

  try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['smtp_port'];

    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addReplyTo($config['reply_email'], $config['reply_name']);
    $mail->addAddress($datos['email'], $datos['nombre']);

    $mail->isHTML(true);
    $asunto = "Confirmación - Hemos recibido tu mensaje";
    $mail->Subject = $asunto;
    $mail->Body = $mensaje;

    // 👉 Agregar cabecera List-Unsubscribe
    $mail->addCustomHeader('List-Unsubscribe', '<mailto:' . $config['from_email'] . '?subject=Unsubscribe>');

    $resultado = $mail->send();

    if ($resultado) {
      Logger::success('email_confirmation', 'Email de confirmación enviado exitosamente', [
        'destinatario' => $datos['email'],
        'asunto' => $asunto
      ]);
    } else {
      Logger::error('email_confirmation', 'Error al enviar email de confirmación', [
        'destinatario' => $datos['email'],
        'asunto' => $asunto
      ]);
    }

    return $resultado;
  } catch (Exception $e) {
    Logger::error('email_confirmation', 'Error al enviar email', [
      'destinatario' => $datos['email'],
      'error' => $e->getMessage()
    ]);
    return false;
  }
}

// ====================================
// FUNCIONES DE BASE DE DATOS
// ====================================

function verificarSiExisteTablaContactos($pdo)
{
  Logger::info('database', 'Verificando existencia de tabla contactos');

  try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'contactos'");
    $existe = $stmt->rowCount() > 0;

    Logger::info('database', 'Verificación de tabla completada', [
      'tabla_existe' => $existe
    ]);

    return $existe;
  } catch (PDOException $e) {
    Logger::error('database', 'Error al verificar tabla contactos', [
      'error' => $e->getMessage(),
      'code' => $e->getCode(),
      'trace' => $e->getTraceAsString()
    ]);
    return false;
  }
}

function crearTablaContactos($pdo)
{
  Logger::info('database', 'Iniciando creación de tabla contactos');

  // Declarar $sql fuera del bloque try para que esté disponible en catch
  $sql = "CREATE TABLE IF NOT EXISTS contactos (
              id INT AUTO_INCREMENT PRIMARY KEY,
              nombre VARCHAR(100) NOT NULL,
              email VARCHAR(255) NOT NULL,
              empresa VARCHAR(255),
              servicio VARCHAR(100),
              mensaje TEXT NOT NULL,
              fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              ip_address VARCHAR(45),
              procesado BOOLEAN DEFAULT FALSE,
              INDEX idx_email (email),
              INDEX idx_fecha (fecha_creacion)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

  try {
    $pdo->exec($sql);

    Logger::success('database', 'Tabla contactos creada exitosamente');
    return true;
  } catch (PDOException $e) {
    Logger::error('database', 'Error al crear tabla contactos', [
      'error' => $e->getMessage(),
      'code' => $e->getCode(),
      'sql' => $sql,
      'trace' => $e->getTraceAsString()
    ]);
    return false;
  }
}

function procesarFormularioTransaccion($datos, $emailConfig, $db_config)
{
  Logger::info('transaction', 'Iniciando procesamiento de formulario con transacción');

  global $db_options;

  try {
    // Conexión a la base de datos
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $db_options);

    Logger::info('transaction', 'Conexión a base de datos establecida', [
      'host' => $db_config['host'],
      'port' => $db_config['port'],
      'database' => $db_config['dbname']
    ]);

    // Verificar y crear tabla si no existe
    if (!verificarSiExisteTablaContactos($pdo)) {
      Logger::info('transaction', 'Tabla no existe, procediendo a crear');
      crearTablaContactos($pdo);
    }

    // Iniciar transacción
    $pdo->beginTransaction();
    Logger::info('transaction', 'Transacción iniciada');

    // Enviar email
    Logger::info('transaction', 'Enviando email dentro de transacción');
    $emailEnviado = enviarEmail($datos, $emailConfig);

    if (!$emailEnviado) {
      $pdo->rollBack();
      Logger::error('transaction', 'Email falló, transacción cancelada');
      return [
        'success' => false,
        'message' => 'Error al enviar el email. Inténtalo de nuevo.'
      ];
    }

    // Insertar en base de datos
    Logger::info('transaction', 'Insertando datos en base de datos');
    $sql = "INSERT INTO contactos (nombre, email, empresa, servicio, mensaje, fecha_creacion, ip_address) 
                    VALUES (?, ?, ?, ?, ?, NOW(), ?)";

    $stmt = $pdo->prepare($sql);
    $guardado = $stmt->execute([
      $datos['nombre'],
      $datos['email'],
      $datos['empresa'],
      $datos['servicio'],
      $datos['mensaje'],
      $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    if (!$guardado) {
      $pdo->rollBack();
      Logger::error('transaction', 'Error al guardar en base de datos, transacción cancelada', [
        'sql' => $sql,
        'datos' => $datos
      ]);
      return [
        'success' => false,
        'message' => 'Error al guardar en la base de datos.'
      ];
    }

    // Confirmar transacción
    $pdo->commit();
    Logger::success('transaction', 'Transacción completada exitosamente', [
      'contact_id' => $pdo->lastInsertId(),
      'email' => $datos['email']
    ]);

    return [
      'success' => true,
      'message' => '¡Gracias por contactarnos! Te responderemos pronto.'
    ];
  } catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
      $pdo->rollBack();
      Logger::error('transaction', 'Transacción cancelada por excepción');
    }

    Logger::error('transaction', 'Error crítico en procesamiento de formulario', [
      'error' => $e->getMessage(),
      'code' => $e->getCode(),
      'trace' => $e->getTraceAsString(),
      'datos' => $datos
    ]);

    return [
      'success' => false,
      'message' => 'Error interno del servidor. Inténtalo más tarde.'
    ];
  }
}

// ====================================
// PROCESAMIENTO PRINCIPAL
// ====================================

// Variables de entorno requeridas
$envPath = __DIR__ . '/../';
$vars = [
  'APP_ENV',
  'DB_HOST',
  'DB_NAME',
  'DB_USER',
  'DB_PASS',
  'DB_PORT',
  'SMTP_HOST',
  'SMTP_PORT',
  'SMTP_USER',
  'SMTP_PASS',
  'FROM_EMAIL',
  'FROM_NAME',
  'TO_EMAIL',
  'TO_NAME',
];

try {
  Logger::info('main', 'Iniciando procesamiento principal del formulario');

  // Cargar configuración
  $config = cargarConfiguracion($envPath, $vars);

  // Configuración de servicios
  $emailConfig = [
    'smtp_host' => $config['SMTP_HOST'],
    'smtp_port' => $config['SMTP_PORT'] ?: 587,
    'smtp_user' => $config['SMTP_USER'],
    'smtp_pass' => $config['SMTP_PASS'],
    'from_email' => $config['FROM_EMAIL'],
    'from_name' => $config['FROM_NAME'],
    'to_email' => $config['TO_EMAIL'],
    'to_name' => $config['TO_NAME'],
    'reply_email' => $config['REPLY_EMAIL'] ?? $config['FROM_EMAIL'],
    'reply_name'  => $config['REPLY_NAME'] ?? $config['FROM_NAME'],
  ];

  $db_config = [
    'host' => $config['DB_HOST'],
    'port' => $config['DB_PORT'] ?: 3306,
    'dbname' => $config['DB_NAME'],
    'username' => $config['DB_USER'],
    'password' => $config['DB_PASS'],
  ];

  $db_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  // Obtener y limpiar datos del formulario
  $datos = limpiarDatos([
    'nombre' => $_POST['nombre'] ?? '',
    'email' => $_POST['email'] ?? '',
    'empresa' => $_POST['empresa'] ?? '',
    'servicio' => $_POST['servicio'] ?? '',
    'mensaje' => $_POST['mensaje'] ?? ''
  ]);

  Logger::info('main', 'Datos del formulario procesados', [
    'campos' => array_keys($datos),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
  ]);

  // Validar campos
  Logger::info('main', 'Iniciando validación de campos');
  $errores = [];

  // Validar nombre
  $erroresNombre = validarCampo('nombre', $datos['nombre'], 'nombre');
  $errores = array_merge($errores, $erroresNombre);

  // Validar email
  $erroresEmail = validarCampo('email', $datos['email'], 'email');
  $errores = array_merge($errores, $erroresEmail);

  // Validar mensaje
  $erroresMensaje = validarCampo('mensaje', $datos['mensaje'], 'mensaje');
  $errores = array_merge($errores, $erroresMensaje);

  // Validar empresa si no está vacía
  if (!empty($datos['empresa'])) {
    $erroresEmpresa = validarCampo('empresa', $datos['empresa'], 'empresa');
    $errores = array_merge($errores, $erroresEmpresa);
    Logger::info('main', 'Campo empresa validado');
  } else {
    Logger::info('main', 'Campo empresa omitido (vacío)');
  }

  // Verificar errores de validación
  if (!empty($errores)) {
    Logger::warning('main', 'Errores de validación encontrados', [
      'total_errores' => count($errores),
      'errores' => $errores,
      'datos_recibidos' => [
        'nombre_length' => strlen($datos['nombre']),
        'email' => $datos['email'],
        'empresa_length' => strlen($datos['empresa']),
        'servicio' => $datos['servicio'],
        'mensaje_length' => strlen($datos['mensaje'])
      ]
    ]);

    http_response_code(400);
    echo json_encode([
      'success' => false,
      'message' => implode('. ', $errores)
    ]);
    exit;
  }

  Logger::success('main', 'Todos los campos validados correctamente');

  // Detectar spam
  Logger::info('main', 'Iniciando detección de spam');
  if (detectarSpam($datos)) {
    Logger::warning('main', 'Mensaje marcado como spam y rechazado', [
      'email' => $datos['email'],
      'nombre' => $datos['nombre'],
      'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    http_response_code(400);
    echo json_encode([
      'success' => false,
      'message' => 'Tu mensaje ha sido marcado como spam'
    ]);
    exit;
  }

  Logger::success('main', 'Verificación de spam completada - mensaje aprobado');

  // Procesar formulario con transacción
  Logger::info('main', 'Iniciando procesamiento con transacción');
  $resultado = procesarFormularioTransaccion($datos, $emailConfig, $db_config);

  // Log del resultado final
  if ($resultado['success']) {
    Logger::success('main', 'Formulario procesado exitosamente', [
      'email' => $datos['email'],
      'nombre' => $datos['nombre'],
      'servicio' => $datos['servicio'],
      'mensaje' => $resultado['message']
    ]);
  } else {
    Logger::error('main', 'Error en procesamiento de formulario', [
      'email' => $datos['email'],
      'nombre' => $datos['nombre'],
      'error_message' => $resultado['message']
    ]);
  }

  // Enviar respuesta
  http_response_code($resultado['success'] ? 200 : 500);
  echo json_encode($resultado);
} catch (Exception $e) {
  Logger::error('main', 'Excepción crítica en procesamiento principal', [
    'error' => $e->getMessage(),
    'code' => $e->getCode(),
    'file' => $e->getFile(),
    'line' => $e->getLine(),
    'trace' => $e->getTraceAsString(),
    'post_data' => $_POST,
    'server_info' => [
      'method' => $_SERVER['REQUEST_METHOD'],
      'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
      'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
      'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]
  ]);

  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'Error interno del servidor. Inténtalo más tarde.'
  ]);
}
