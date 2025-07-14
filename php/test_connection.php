<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Datos de conexión usando el host público
    $host = 'maglev.proxy.rlwy.net';
    $db   = 'railway';
    $user = 'root';
    $pass = 'ljshGjWuTuYNzlviwEyIrbEbhaBiifyC';
    $port = 49609;
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        echo "✅ Conexión exitosa.<br>";

        // Crear tabla "usuarios"
        $sql = "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $pdo->exec($sql);

        echo "✅ Tabla 'usuarios' creada correctamente (o ya existía).";
    } catch (\PDOException $e) {
        echo "❌ Error de conexión o al crear tabla: " . $e->getMessage();
    }
} else {
    echo "⚠️ Método no permitido. Solo se acepta POST.";
}
