<?php
// php/middleware/verify_recaptcha.php

function verifyRecaptcha(array $postData, string $remoteIp, string $secret): array {
    if (empty($postData['g-recaptcha-response'])) {
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
        return ['success' => false, 'message' => 'Error: Verificación reCAPTCHA fallida.', 'details' => $resultJson];
    }

    return ['success' => true];
}
