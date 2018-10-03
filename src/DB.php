<?php

namespace Uspdev\Replicado;

use PDO;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DB
{
    private static $instance;
    private function __construct(){}
    private function __clone(){}
    private static $logger;

    public static function getInstance(){
        $type = getenv('REPLICADO_SGBD');
        $host = getenv('REPLICADO_HOST');
        $port = getenv('REPLICADO_PORT');
        $db   = getenv('REPLICADO_DATABASE');
        $user = getenv('REPLICADO_USERNAME');
        $pass = getenv('REPLICADO_PASSWORD');

        if (!self::$instance) {
            try {
                if($type == 'fflch') {
                    $dsn = "dblib:tdsver=5.0;host={$host}:{$port}";
                    self::$instance = new PDO($dsn,$user,$pass);
                    self::$instance->query("use {$db}");
                } else {
                    $dsn = "dblib:host={$host}:{$port};dbname={$db}";
                    self::$instance = new PDO($dsn,$user,$pass);
                }
                self::$instance->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            } catch (\Throwable $t) {
                echo "Erro na conexão com o database! Contate o suporte";
                $log = self::getLogger('Conexão');
                $log->error($t->getMessage());
                die();
            }
        }
        return self::$instance;
    }

    // overhide fetch and fetchAll functions
    public static function fetch(string $query, array $param = null)
    {
        try {
            $stmt = self::getInstance()->prepare($query);
            if (!is_null($param)) {
                foreach ($param as $campo => $valor) {
                    $stmt->bindValue(":$campo", $valor);
                }
            }
            $stmt->execute();
        } catch (\Throwable $t) {
            echo "Erro Interno: contate o suporte!";
            $log = self::getLogger('Consulta');
            $log->error($t->getMessage());
            return false;
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function fetchAll(string $query, array $param = null)
    {
        try {
            $stmt = self::getInstance()->prepare($query);
            if (!is_null($param)) {
                foreach ($param as $campo => $valor) {
                    $stmt->bindValue(":$campo", $valor);
                }
            }
            $stmt->execute();
        } catch (\Throwable $t) {
            echo "Erro Interno: contate o suporte!";
            $log = self::getLogger('Consulta');
            $log->error($t->getMessage());
            return false;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function getLogger($channel_name)
    {
        if (!isset(self::$logger)) {
            self::$logger = new Logger($channel_name);
            self::$logger->pushHandler(new StreamHandler(__DIR__ . '/log/replicado.log', Logger::DEBUG));
        }
        return self::$logger;
    }
}
