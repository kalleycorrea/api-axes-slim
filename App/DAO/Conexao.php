<?php

namespace App\DAO;

abstract class Conexao
{
    /**
     * @var \PDO
     */
    protected $pdoRbx;
    /**
     * @var \PDO
     */
    protected $pdoAxes;

    public function __construct()
    {
        $hostRbx = getenv('MYSQL_HOST_RBX');
        $portRbx = getenv('MYSQL_PORT_RBX');
        $userRbx = getenv('MYSQL_USER_RBX');
        $passRbx = getenv('MYSQL_PASSWORD_RBX');
        $dbnameRbx = getenv('MYSQL_DBNAME_RBX');

        $dsnRbx = "mysql:host={$hostRbx};dbname={$dbnameRbx};port={$portRbx};charset=UTF8";

        $this->pdoRbx = new \PDO($dsnRbx, $userRbx, $passRbx);
        $this->pdoRbx->setAttribute(
            \PDO::ATTR_ERRMODE,
            \PDO::ERRMODE_EXCEPTION
        );

        $hostAxes = getenv('MYSQL_HOST_AXES');
        $portAxes = getenv('MYSQL_PORT_AXES');
        $userAxes = getenv('MYSQL_USER_AXES');
        $passAxes = getenv('MYSQL_PASSWORD_AXES');
        $dbnameAxes = getenv('MYSQL_DBNAME_AXES');

        $dsnAxes = "mysql:host={$hostAxes};dbname={$dbnameAxes};port={$portAxes};charset=UTF8";

        $this->pdoAxes = new \PDO($dsnAxes, $userAxes, $passAxes);
        $this->pdoAxes->setAttribute(
            \PDO::ATTR_ERRMODE,
            \PDO::ERRMODE_EXCEPTION
        );
    }
}
