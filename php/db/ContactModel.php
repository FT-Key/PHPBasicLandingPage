<?php

require_once __DIR__ . '/../core/Logger.php';

class ContactModel
{
  private $pdo;

  public function __construct($pdo)
  {
    $this->pdo = $pdo;
  }

  public function verifyTableExists()
  {
    Logger::info('database', 'Verificando existencia de tabla contactos');

    $stmt = $this->pdo->query("SHOW TABLES LIKE 'contactos'");
    return $stmt->rowCount() > 0;
  }

  public function createTable()
  {
    $sql = "CREATE TABLE IF NOT EXISTS contactos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            empresa VARCHAR(255),
            servicio VARCHAR(100),
            mensaje TEXT NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            procesado BOOLEAN DEFAULT FALSE,
            INDEX idx_email (email),
            INDEX idx_fecha (fecha_creacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $this->pdo->exec($sql);
    Logger::success('database', 'Tabla contactos creada o verificada');
  }

  public function insert($datos)
  {
    $sql = "INSERT INTO contactos (nombre, email, empresa, servicio, mensaje, fecha_creacion, ip_address)
                VALUES (?, ?, ?, ?, ?, NOW(), ?)";

    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
      $datos['nombre'],
      $datos['email'],
      $datos['empresa'],
      $datos['servicio'],
      $datos['mensaje'],
      $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
  }
}
