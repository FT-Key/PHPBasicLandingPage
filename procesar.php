<?php
// ====================================
// PROCESAMIENTO DE FORMULARIO PHP
// ====================================

// Configuración de errores (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Configuración de headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Método no permitido']);
  exit;
}

// ====================================
// CONFIGURACIÓN
// ====================================

// Configuración de email
$config = [
  'smtp_host' => 'smtp.gmail.com',
  'smtp_port' => 587,
  'smtp_user' => 'tu-email@gmail.com',
  'smtp_pass' => 'tu-password-de-aplicacion',
  'from_email' => 'tu-email@gmail.com',
  'from_name' => 'TechSolutions',
  'to_email' => 'contacto@techsolutions.com',
  'to_name' => 'TechSolutions'
];

// Configuración de base de datos (opcional)
$db_config = [
  'host' => 'localhost',
  'dbname' => 'techsolutions',
  'username' => 'tu_usuario',
  'password' => 'tu_password'
];

// ====================================
// FUNCIONES DE VALIDACIÓN
// ====================================

function validarCampo($campo, $valor, $tipo = 'texto')
{
  $errores = [];

  // Validar campo requerido
  if (empty(trim($valor))) {
    $errores[] = "El campo {$campo} es obligatorio";
    return $errores;
  }

  switch ($tipo) {
    case 'email':
      if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no tiene un formato válido";
      }
      break;

    case 'nombre':
      if (strlen($valor) < 2 || strlen($valor) > 50) {
        $errores[] = "El nombre debe tener entre 2 y 50 caracteres";
      }
      if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $valor)) {
        $errores[] = "El nombre solo puede contener letras y espacios";
      }
      break;

    case 'mensaje':
      if (strlen($valor) < 10) {
        $errores[] = "El mensaje debe tener al menos 10 caracteres";
      }
      if (strlen($valor) > 1000) {
        $errores[] = "El mensaje no puede exceder 1000 caracteres";
      }
      break;

    case 'empresa':
      if (strlen($valor) > 100) {
        $errores[] = "El nombre de la empresa no puede exceder 100 caracteres";
      }
      break;
  }

  return $errores;
}

function limpiarDatos($datos)
{
  return array_map(function ($valor) {
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
  }, $datos);
}

function detectarSpam($datos)
{
  // Detectar patrones de spam
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
      return true;
    }
  }

  return false;
}

// ====================================
// PROCESAMIENTO PRINCIPAL
// ====================================

try {
  // Obtener y limpiar datos del formulario
  $datos = limpiarDatos([
    'nombre' => $_POST['nombre'] ?? '',
    'email' => $_POST['email'] ?? '',
    'empresa' => $_POST['empresa'] ?? '',
    'servicio' => $_POST['servicio'] ?? '',
    'mensaje' => $_POST['mensaje'] ?? ''
  ]);

  // Validar todos los campos
  $errores = [];

  // Validar campos obligatorios
  $errores = array_merge($errores, validarCampo('nombre', $datos['nombre'], 'nombre'));
  $errores = array_merge($errores, validarCampo('email', $datos['email'], 'email'));
  $errores = array_merge($errores, validarCampo('mensaje', $datos['mensaje'], 'mensaje'));

  // Validar campos opcionales si no están vacíos
  if (!empty($datos['empresa'])) {
    $errores = array_merge($errores, validarCampo('empresa', $datos['empresa'], 'empresa'));
  }

  // Verificar si hay errores de validación
  if (!empty($errores)) {
    echo json_encode([
      'success' => false,
      'message' => implode('. ', $errores)
    ]);
    exit;
  }

  // Detectar spam
  if (detectarSpam($datos)) {
    echo json_encode([
      'success' => false,
      'message' => 'Tu mensaje ha sido marcado como spam'
    ]);
    exit;
  }

  // Procesar el formulario
  $resultado_email = enviarEmail($datos, $config);
  $resultado_db = guardarEnBaseDatos($datos, $db_config);

  if ($resultado_email) {
    echo json_encode([
      'success' => true,
      'message' => '¡Gracias por contactarnos! Te responderemos pronto.',
      'data' => [
        'email_enviado' => $resultado_email,
        'guardado_bd' => $resultado_db
      ]
    ]);
  } else {
    echo json_encode([
      'success' => false,
      'message' => 'Error al enviar el email. Inténtalo de nuevo.'
    ]);
  }
} catch (Exception $e) {
  error_log("Error en procesamiento de formulario: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Error interno del servidor. Inténtalo más tarde.'
  ]);
}

// ====================================
// FUNCIÓN PARA ENVIAR EMAIL
// ====================================

function enviarEmail($datos, $config)
{
  try {
    // Configurar contenido del email
    $asunto = "Nuevo contacto desde TechSolutions - " . $datos['nombre'];

    $mensaje_html = generarPlantillaEmail($datos);

    // Headers del email
    $headers = [
      'MIME-Version: 1.0',
      'Content-type: text/html; charset=UTF-8',
      'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
      'Reply-To: ' . $datos['email'],
      'X-Mailer: PHP/' . phpversion()
    ];

    // Enviar email
    $enviado = mail($config['to_email'], $asunto, $mensaje_html, implode("\r\n", $headers));

    if ($enviado) {
      // Enviar email de confirmación al cliente
      enviarEmailConfirmacion($datos, $config);
      return true;
    }

    return false;
  } catch (Exception $e) {
    error_log("Error al enviar email: " . $e->getMessage());
    return false;
  }
}

function generarPlantillaEmail($datos)
{
  $servicios = [
    'desarrollo-web' => 'Desarrollo Web',
    'apps-moviles' => 'Apps Móviles',
    'cloud-solutions' => 'Cloud Solutions',
    'consultoria' => 'Consultoría'
  ];

  $servicio_nombre = $servicios[$datos['servicio']] ?? 'No especificado';

  return "
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
}

function enviarEmailConfirmacion($datos, $config)
{
  $asunto = "Confirmación - Hemos recibido tu mensaje";

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
            </div>
            
            <div class='footer'>
                <p>TechSolutions - Transformando ideas en soluciones digitales</p>
            </div>
        </div>
    </body>
    </html>
    ";

  $headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
    'X-Mailer: PHP/' . phpversion()
  ];

  return mail($datos['email'], $asunto, $mensaje, implode("\r\n", $headers));
}

// ====================================
// FUNCIÓN PARA GUARDAR EN BASE DE DATOS
// ====================================

function guardarEnBaseDatos($datos, $db_config)
{
  try {
    // Conectar a la base de datos
    $pdo = new PDO(
      "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8",
      $db_config['username'],
      $db_config['password'],
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
      ]
    );

    // Preparar consulta
    $sql = "INSERT INTO contactos (nombre, email, empresa, servicio, mensaje, fecha_creacion, ip_address) 
                VALUES (?, ?, ?, ?, ?, NOW(), ?)";

    $stmt = $pdo->prepare($sql);

    // Ejecutar consulta
    $resultado = $stmt->execute([
      $datos['nombre'],
      $datos['email'],
      $datos['empresa'],
      $datos['servicio'],
      $datos['mensaje'],
      $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    return $resultado;
  } catch (PDOException $e) {
    error_log("Error en base de datos: " . $e->getMessage());
    return false;
  }
}

// ====================================
// FUNCIÓN PARA CREAR TABLA (EJECUTAR UNA SOLA VEZ)
// ====================================

function crearTablaContactos($db_config)
{
  try {
    $pdo = new PDO(
      "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8",
      $db_config['username'],
      $db_config['password']
    );

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

    $pdo->exec($sql);
    return true;
  } catch (PDOException $e) {
    error_log("Error al crear tabla: " . $e->getMessage());
    return false;
  }
}

// Descomenta esta línea para crear la tabla la primera vez
// crearTablaContactos($db_config);
