<?php

namespace Uspdev\Replicado;
use PDO;

class DB
{
    private static $instance;
    private function __construct(){}
    private function __clone(){}

    public static function getInstance(){
        $type = getenv('REPLICADO_SGBD');
        $host = getenv('REPLICADO_HOST');
        $port = getenv('REPLICADO_PORT');
        $db   = getenv('REPLICADO_DATABASE');
        $user = getenv('REPLICADO_USERNAME');
        $pass = getenv('REPLICADO_PASSWORD');

        if(!self::$instance){
            try {
                if($type == 'fflch') {
                    $dsn = "dblib:tdsver=5.0;host={$host}:{$port}";
                    self::$instance = new PDO($dsn,$user,$pass);
                    self::$instance->query("use {$db}");
                } else 
                if($type == 'default') {
                    $dsn = "dblib:host={$host}:{$port};dbname={$db}";
                    self::$instance = new PDO($dsn,$user,$pass);
                }
                else {
                    die("database type not set: use default");
                }
                self::$instance->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                print_r($e->getMessage());
                die();
            }
        }
        return self::$instance;
    }

    // overhide fetch and fetchAll functions
    public static function fetch(string $query)
    {
        $stmt = self::getInstance()->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function fetchAll(string $query)
    {
        $stmt = self::getInstance()->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
