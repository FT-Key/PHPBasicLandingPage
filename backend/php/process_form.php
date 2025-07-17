<?php

require_once __DIR__ . '/security/block_direct_access.php';
require_once __DIR__ . '/security/check_payload_size.php';
require_once __DIR__ . '/security/validate_content_type.php';
validarMetodoYContentType();

require_once __DIR__ . '/config/config_loader.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/ResponseHelper.php';
require_once __DIR__ . '/db/Database.php';
require_once __DIR__ . '/db/ContactModel.php';
require_once __DIR__ . '/mail/Mailer.php';
require_once __DIR__ . '/validation/Validator.php';
require_once __DIR__ . '/validation/SpamDetector.php';

Logger::init(__DIR__ . '/');

try {
  $envPath = __DIR__ . '/../';
  $vars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT', 'SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'FROM_EMAIL', 'FROM_NAME', 'TO_EMAIL', 'TO_NAME'];
  $config = cargarConfiguracion($envPath, $vars);

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

  $database = new Database($db_config, $db_options);
  $pdo = $database->getConnection();
  $contactModel = new ContactModel($pdo);

  if (!$contactModel->verifyTableExists()) {
    $contactModel->createTable();
  }

  $datos = Validator::limpiarDatos($_POST);
  $errores = [];

  $errores = array_merge($errores, Validator::validarCampo('nombre', $datos['nombre'], 'nombre'));
  $errores = array_merge($errores, Validator::validarCampo('email', $datos['email'], 'email'));
  $errores = array_merge($errores, Validator::validarCampo('mensaje', $datos['mensaje'], 'mensaje'));

  if (!empty($errores)) {
    ResponseHelper::json(false, implode('. ', $errores), 400);
  }

  if (SpamDetector::esSpam($datos)) {
    ResponseHelper::json(false, 'Tu mensaje ha sido marcado como spam', 400);
  }

  // --- Mapear config para Mailer ---
  $mailerConfig = [
    'smtp_host' => $config['SMTP_HOST'] ?? '',
    'smtp_user' => $config['SMTP_USER'] ?? '',
    'smtp_pass' => $config['SMTP_PASS'] ?? '',
    'smtp_port' => $config['SMTP_PORT'] ?? 587,
    'from_email' => $config['FROM_EMAIL'] ?? '',
    'from_name' => $config['FROM_NAME'] ?? '',
    'to_email' => $config['TO_EMAIL'] ?? '',
    'to_name' => $config['TO_NAME'] ?? '',
    'reply_email' => $config['FROM_EMAIL'] ?? '',
    'reply_name' => $config['FROM_NAME'] ?? '',
  ];

  $mailer = new Mailer($mailerConfig);

  $pdo->beginTransaction();

  $mailer->sendContactMail($datos);
  $mailer->sendConfirmation($datos);

  $contactModel->insert($datos);

  $pdo->commit();

  ResponseHelper::json(true, '¡Gracias por contactarnos! Te responderemos pronto.');
} catch (Exception $e) {
  if (isset($pdo) && $pdo->inTransaction()) {
    $pdo->rollBack();
  }
  Logger::error('main', 'Excepción en formulario', ['error' => $e->getMessage()]);
  ResponseHelper::json(false, 'Error interno del servidor', 500);
}
