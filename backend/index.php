<?php
// backend/index.php

require_once __DIR__ . '/vendor/autoload.php'; // ✅ Esto carga Dotenv y demás
require_once __DIR__ . '/php/core/Logger.php';
Logger::init(__DIR__ . '/php');
require_once __DIR__ . '/php/security/headers.php';
require_once __DIR__ . '/php/core/ResponseHelper.php';


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Mostrar página de estado
if ($method === 'GET' && ($uri === '' || $uri === '/')) {
  $statusFile = __DIR__ . '/status/status.html';
  if (file_exists($statusFile)) {
    header('Content-Type: text/html');
    readfile($statusFile);
    exit;
  } else {
    http_response_code(500);
    echo 'Error: status.html no encontrado.';
    exit;
  }
}

// GET /api/hola
if ($method === 'GET' && $uri === '/api/hola') {
  ResponseHelper::json(true, 'Hola Mundo!');
}

// POST endpoints que requieren recaptcha
if ($method === 'POST' && in_array($uri, ['/api/solicitar-servicio', '/api/otro-endpoint'])) {

  require_once __DIR__ . '/php/middleware/verify_recaptcha.php';
  require_once __DIR__ . '/php/config/config_loader.php';

  $envPath = realpath(__DIR__);
  $config = cargarConfiguracion($envPath, ['RECAPTCHA_SECRET']);
  $secret = $config['RECAPTCHA_SECRET'] ?? '';

  $recaptchaResult = verifyRecaptcha($_POST, $_SERVER['REMOTE_ADDR'], $secret);

  if (!$recaptchaResult['success']) {
    ResponseHelper::json(false, $recaptchaResult['message'], 400);
  }

  // Si pasa reCAPTCHA, ejecutar el procesamiento específico
  switch ($uri) {
    case '/api/solicitar-servicio':
      require_once __DIR__ . '/php/process_form.php';
      break;
    case '/api/otro-endpoint':
      require_once __DIR__ . '/php/process_other_form.php';
      break;
  }
  exit;
}

// Ruta no encontrada
ResponseHelper::json(false, 'Ruta no encontrada o método no permitido', 404);
