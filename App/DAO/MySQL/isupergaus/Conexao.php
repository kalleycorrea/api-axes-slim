<?php

namespace App\DAO\MySQL\isupergaus;

abstract class Conexao
{
    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct()
    {
        $host = getenv('AXES_DB_MYSQL_HOST');
        $port = getenv('AXES_DB_MYSQL_PORT');
        $user = getenv('AXES_DB_MYSQL_USER');
        $pass = getenv('AXES_DB_MYSQL_PASSWORD');
        $dbname = getenv('AXES_DB_MYSQL_DBNAME');

        $dsn = "mysql:host={$host};dbname={$dbname};port={$port};charset=UTF8";

        $this->pdo = new \PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(
            \PDO::ATTR_ERRMODE,
            \PDO::ERRMODE_EXCEPTION
        );
    }
}
