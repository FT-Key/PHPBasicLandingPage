<?php
$maxSize = 1048576;
if ($_SERVER['CONTENT_LENGTH'] > $maxSize) {
  Logger::error('security', 'Payload demasiado grande');
  http_response_code(413);
  echo json_encode(['success' => false, 'message' => 'El mensaje es demasiado grande']);
  exit;
}
