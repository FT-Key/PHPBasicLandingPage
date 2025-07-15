<?php
// verify_recaptcha.php

// Incluye el autoload de composer para que funcione Dotenv y Logger
require_once __DIR__ . '/../../vendor/autoload.php';

// Incluye Logger (ajusta ruta si es necesario)
require_once __DIR__ . '/../logger/Logger.php';
Logger::init(__DIR__ . '/../logs');

// Incluye el cargador de configuración
require_once __DIR__ . '/../config/config_loader.php';

// Carga las variables que necesitas, la ruta debe apuntar a donde está tu .env
// Por ejemplo si .env está en la raíz del proyecto
$envPath = realpath(__DIR__ . '/../../'); // Ajusta si no es correcto

$config = cargarConfiguracion($envPath, ['RECAPTCHA_SECRET']);

// Clave secreta desde configuración
$secret = $config['RECAPTCHA_SECRET'] ?? '';

if (empty($secret)) {
  Logger::error('recaptcha', 'No se encontró la clave secreta de reCAPTCHA en configuración');
  jsonResponse(false, 'Error: Clave secreta no configurada.');
}

// ✅ Incluir rate limit antes que nada
require_once __DIR__ . '/../security/rate_limit.php';

// Verificar que existe la respuesta
if (empty($_POST['g-recaptcha-response'])) {
  jsonResponse(false, 'Error: Completa el reCAPTCHA.');
}

$response = $_POST['g-recaptcha-response'];

// Petición a Google para verificar
$remoteip = $_SERVER['REMOTE_ADDR'];
$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

$data = [
  'secret' => $secret,
  'response' => $response,
  'remoteip' => $remoteip
];

$options = [
  'http' => [
    'method' => 'POST',
    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
    'content' => http_build_query($data)
  ]
];
$context = stream_context_create($options);
$result = file_get_contents($verifyUrl, false, $context);
$resultJson = json_decode($result, true);

if ($resultJson['success'] !== true) {
  Logger::warning('recaptcha', 'Fallo verificación reCAPTCHA', ['response' => $resultJson]);
  jsonResponse(false, 'Error: Verificación reCAPTCHA fallida.');
}

// ✅ Si pasa reCAPTCHA, incluí el process_form.php
require_once __DIR__ . '/../process_form.php';
