<?php
// php/middleware/verify_recaptcha.php

require_once __DIR__ . '/../core/Logger.php';

function verifyRecaptcha(array $postData, string $remoteIp, string $secret): array
{
  if (empty($postData['g-recaptcha-response'])) {
    Logger::warning('recaptcha', 'Falta el token de reCAPTCHA', [
      'ip' => $remoteIp
    ]);
    return ['success' => false, 'message' => 'Error: Completa el reCAPTCHA.'];
  }

  $response = $postData['g-recaptcha-response'];
  $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

  $data = [
    'secret' => $secret,
    'response' => $response,
    'remoteip' => $remoteIp
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

  if (!isset($resultJson['success']) || $resultJson['success'] !== true) {
    Logger::warning('recaptcha', 'Verificación fallida', [
      'ip' => $remoteIp,
      'response' => $resultJson
    ]);
    return [
      'success' => false,
      'message' => 'Error: Verificación reCAPTCHA fallida.',
      'details' => $resultJson
    ];
  }

  Logger::success('recaptcha', 'Verificación exitosa', [
    'ip' => $remoteIp
  ]);

  return ['success' => true];
}
