<?php
session_start();

// Limite: 1 solicitud cada 30 segundos
$limiteTiempo = 30; // segundos

if (isset($_SESSION['ultimo_envio'])) {
  $diferencia = time() - $_SESSION['ultimo_envio'];

  if ($diferencia < $limiteTiempo) {
    http_response_code(429); // Too Many Requests
    die(json_encode([
      'status' => 'error',
      'message' => "⛔ Por favor, esperá {$limiteTiempo} segundos entre envíos."
    ]));
  }
}

// Si pasó, actualizamos el timestamp
$_SESSION['ultimo_envio'] = time();
