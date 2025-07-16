<?php

class Database
{
  private $pdo;

  public function __construct($db_config, $db_options)
  {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset=utf8mb4";
    $this->pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $db_options);
  }

  public function getConnection()
  {
    return $this->pdo;
  }
}
