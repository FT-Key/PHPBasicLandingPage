<?php
// php/validation/Validator.php

require_once __DIR__ . '/../core/Logger.php';

class Validator
{
  public static function validarCampo($campo, $valor, $tipo = 'texto')
  {
    Logger::info('validation', 'Validando campo', [
      'campo' => $campo,
      'tipo' => $tipo,
      'valor_length' => strlen($valor)
    ]);

    $errores = [];

    if (empty(trim($valor))) {
      $errores[] = "El campo {$campo} es obligatorio";
      Logger::warning('validation', 'Campo requerido vacío', ['campo' => $campo]);
      return $errores;
    }

    switch ($tipo) {
      case 'email':
        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
          $errores[] = "El email no tiene un formato válido";
          Logger::warning('validation', 'Email inválido', ['campo' => $campo, 'valor' => $valor]);
        }
        break;

      case 'nombre':
        if (strlen($valor) < 2 || strlen($valor) > 50) {
          $errores[] = "El nombre debe tener entre 2 y 50 caracteres";
        }
        if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/', $valor)) {
          $errores[] = "El nombre solo puede contener letras y espacios";
        }
        break;

      case 'mensaje':
        if (strlen($valor) < 10) {
          $errores[] = "El mensaje debe tener al menos 10 caracteres";
        }
        if (strlen($valor) > 1000) {
          $errores[] = "El mensaje no puede exceder 1000 caracteres";
        }
        break;

      case 'empresa':
        if (strlen($valor) > 100) {
          $errores[] = "El nombre de la empresa no puede exceder 100 caracteres";
        }
        break;
    }

    if (empty($errores)) {
      Logger::success('validation', 'Campo validado correctamente', ['campo' => $campo]);
    }

    return $errores;
  }

  public static function limpiarDatos($datos)
  {
    Logger::info('sanitization', 'Iniciando limpieza de datos', ['campos' => array_keys($datos)]);
    return array_map(function ($valor) {
      return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }, $datos);
  }
}
