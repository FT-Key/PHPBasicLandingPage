<?php
function validarMetodoYContentType()
{
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido.']));
  }

  $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

  if (
    stripos($contentType, 'application/x-www-form-urlencoded') === false &&
    stripos($contentType, 'multipart/form-data') === false
  ) {
    http_response_code(415);
    die(json_encode(['error' => 'Content-Type no permitido.']));
  }
}
