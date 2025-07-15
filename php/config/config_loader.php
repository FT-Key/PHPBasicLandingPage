<?php
// php/config/config_loader.php

require_once __DIR__ . '/../logger/Logger.php'; // Ajusta ruta si es necesario

use Dotenv\Dotenv;

function cargarConfiguracion($envPath, $vars)
{
  Logger::info('config', 'Iniciando carga de configuración', ['env_path' => $envPath]);

  try {
    $isLocal = file_exists($envPath . '/.env');

    Logger::info('config', 'Entorno detectado', [
      'is_local' => $isLocal,
      'env_file_exists' => $isLocal
    ]);

    if ($isLocal) {
      $dotenv = Dotenv::createImmutable($envPath);
      $dotenv->load();
      Logger::info('config', 'Variables de entorno cargadas desde archivo .env');
    }

    $config = [];
    $missing_vars = [];

    foreach ($vars as $var) {
      $value = $_ENV[$var] ?? getenv($var) ?: '';
      $config[$var] = $value;

      if (empty($value)) {
        $missing_vars[] = $var;
      }
    }

    if (!empty($missing_vars)) {
      Logger::warning('config', 'Variables de entorno faltantes', [
        'missing_variables' => $missing_vars
      ]);
    }

    Logger::success('config', 'Configuración cargada exitosamente', [
      'loaded_vars' => count($config),
      'missing_vars' => count($missing_vars)
    ]);

    return $config;
  } catch (Exception $e) {
    Logger::error('config', 'Error al cargar configuración', [
      'error' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
    ]);
    throw $e;
  }
}

function jsonResponse($success, $message)
{
  header('Content-Type: application/json');
  echo json_encode(['success' => $success, 'message' => $message]);
  exit;
}
