<?php
// verify_recaptcha.php

// ✅ Incluir rate limit antes que nada
require_once __DIR__ . '/../security/rate_limit.php';

// Clave secreta de tu reCAPTCHA (la que te da Google)
$secret = '6LfxOYQrAAAAANg-8rNkWaZgL5f8dFDUne0PmT-_';

// Verificar que existe la respuesta
if (empty($_POST['g-recaptcha-response'])) {
  die('Error: Completa el reCAPTCHA.');
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
  die('Error: Verificación reCAPTCHA fallida.');
}

// ✅ Si pasa reCAPTCHA, incluí el process_form.php
require_once __DIR__ . '/../process_form.php';
