<?php

class ResponseHelper
{
  public static function json($success, $message, $code = 200)
  {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
  }
}
