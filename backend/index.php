<?php
// backend/index.php

// Permitir CORS desde cualquier origen (para desarrollo)
require_once __DIR__ . '/php/security/headers.php';

// Si es una petición OPTIONS, solo responde y termina (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Mostrar página de estado (loader) en la raíz
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

// Ruta: GET /api/hola
if ($method === 'GET' && $uri === '/api/hola') {
  header('Content-Type: application/json');
  echo json_encode(['mensaje' => 'Hola Mundo!']);
  exit;
}

// Ruta: POST /api/solicitar-servicio
if ($method === 'POST' && $uri === '/api/solicitar-servicio') {
  require_once __DIR__ . '/php/middleware/verify_recaptcha.php';
  exit;
}

// Ruta no encontrada
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
  'success' => false,
  'error' => 'Ruta no encontrada o método no permitido'
]);
