<?php
// headers.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Configurar manejo de errores para evitar HTML en la respuesta
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Limpiar cualquier output previo
ob_clean();
