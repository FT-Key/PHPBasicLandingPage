<?php
// php/core/Logger.php

class Logger
{
  private static $logDirectory;
  private static $initialized = false;

  public static function init()
  {
    if (self::$initialized) return;

    $logPath = __DIR__ . '/../logs';
    self::$logDirectory = realpath($logPath);

    if (!self::$logDirectory) {
      self::$logDirectory = $logPath;
      mkdir(self::$logDirectory, 0755, true);
    }

    // Proteger directorio en Apache
    $htaccessPath = self::$logDirectory . '/.htaccess';
    if (!file_exists($htaccessPath)) {
      file_put_contents($htaccessPath, "Deny from all\n");
    }

    self::$initialized = true;
  }

  public static function log($category, $message, $data = null, $level = 'INFO')
  {
    self::init();

    $timestamp = date('Y-m-d H:i:s');
    $logFile = self::$logDirectory . '/' . $category . '_' . date('Y-m-d') . '.log';

    $logEntry = [
      'timestamp' => $timestamp,
      'level' => $level,
      'message' => $message,
      'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
      'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];

    if ($data !== null) {
      $logEntry['data'] = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
    }

    $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";

    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
  }

  public static function error($category, $message, $data = null)
  {
    self::log($category, $message, $data, 'ERROR');
  }

  public static function warning($category, $message, $data = null)
  {
    self::log($category, $message, $data, 'WARNING');
  }

  public static function info($category, $message, $data = null)
  {
    self::log($category, $message, $data, 'INFO');
  }

  public static function success($category, $message, $data = null)
  {
    self::log($category, $message, $data, 'SUCCESS');
  }

  public static function logException($category, \Throwable $e)
  {
    self::log($category, 'Excepción atrapada: ' . $e->getMessage(), [
      'file' => $e->getFile(),
      'line' => $e->getLine(),
      'trace' => $e->getTraceAsString()
    ], 'EXCEPTION');
  }
}

