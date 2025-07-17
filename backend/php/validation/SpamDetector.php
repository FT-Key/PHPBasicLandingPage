<?php
// php/security/SpamDetector.php

require_once __DIR__ . '/../core/Logger.php';

class SpamDetector
{
  public static function esSpam($datos)
  {
    Logger::info('spam_detection', 'Iniciando detección de spam');

    $spamPatterns = [
      'http://',
      'https://',
      'www.',
      'click here',
      'free money',
      'viagra',
      'casino'
    ];

    $contenido = strtolower(implode(' ', $datos));

    foreach ($spamPatterns as $pattern) {
      if (strpos($contenido, $pattern) !== false) {
        Logger::warning('spam_detection', 'Contenido marcado como spam', ['pattern_found' => $pattern]);
        return true;
      }
    }

    Logger::success('spam_detection', 'Contenido aprobado - no es spam');
    return false;
  }
}
