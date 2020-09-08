<?php

namespace Uspdev\Replicado;

use PDO;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DB
{
    private static $instance;
    private static $sgbd;
    private function __construct(){}
    private function __clone(){}
    private static $logger;
       
    public static function getInstance(){
        $host = getenv('REPLICADO_HOST');
        $port = getenv('REPLICADO_PORT');
        $db   = getenv('REPLICADO_DATABASE');
        $user = getenv('REPLICADO_USERNAME');
        $pass = getenv('REPLICADO_PASSWORD');

        if (!self::$instance) {
            try {
                $dsn = "dblib:host={$host}:{$port};dbname={$db};charset=UTF-8";
                self::$instance = new PDO($dsn,$user,$pass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            } catch (\Throwable $t) {
                echo "Erro na conexão com o database do replicado! Contate o suporte";
                $log = self::getLogger('Conexão');
                $log->error($t->getMessage());
                die();
            }
        }
        return self::$instance;
    }

    public static function sybase() {
        return getenv('REPLICADO_SYBASE') ?? 0;
    }

    // overhide fetch and fetchAll functions
    public static function fetch(string $query, array $param = null)
    {
        try {
            $stmt = self::getInstance()->prepare($query);
            if (!is_null($param)) {
                foreach ($param as $campo => $valor) {
                    $valor = DB::sybase() ? utf8_decode($valor) : $valor;
                    $stmt->bindValue(":$campo", $valor);
                }
            }
            $stmt->execute();
        } catch (\Throwable $t) {
            echo "Erro Interno no replicado: contate o suporte!";
            $log = self::getLogger('Consulta');
            $log->error($t->getMessage());
            return false;
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($result) && self::sybase()) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result); 
        }

        return $result;
    }

    public static function fetchAll(string $query, array $param = null)
    {
        try {
            $stmt = self::getInstance()->prepare($query);
            if (!is_null($param)) {
                foreach ($param as $campo => $valor) {
                    $valor = DB::sybase() ? utf8_decode($valor) : $valor;
                    $stmt->bindValue(":$campo", $valor);
                }
            }
            $stmt->execute();
        } catch (\Throwable $t) {
            echo "Erro Interno no replicado: contate o suporte!";
            $log = self::getLogger('Consulta');
            $log->error($t->getMessage());
            return false;
        }

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($result) && self::sybase()) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result); 
        }
        return $result;
    }

    private static function getLogger($channel_name)
    {
        if (!isset(self::$logger)) {
            $pathlog = getenv('REPLICADO_PATHLOG') ?: '/tmp/replicado.log';
            self::$logger = new Logger($channel_name);
            self::$logger->pushHandler(new StreamHandler($pathlog, Logger::DEBUG));
        }
        return self::$logger;
    }

    /**
     * Retorna array contendo string formatada do WHERE com os filtros/buscas e 
     * as colunas => valores no formato para 'bind'
     *  
     * @param array $filtros (opcional) - campo_tabela => valor
     * @param array $buscas (opcional) - campo_tabela => valor
     * @param array $tipos (opcional) - campo_tabela => tipo (ex.: codpes => int)
     * 
     * @return array posição [0] => string WHERE, posição [1] = 'colunas' => valores
     *
     */
    public static function criaFiltroBusca(array $filtros, array $buscas, array $tipos)
    {
        // Abre o parênteses dos filtros
        $str_where = "";
        $params = [];
        if (!empty($filtros) && (count($filtros) > 0)) {
            $str_where .= " WHERE (";
            foreach ($filtros as $coluna => $valor) {
                if (array_key_exists($coluna, $tipos)) {
                    $str_where .= " {$coluna} = convert({$tipos[$coluna]}, :{$coluna}) ";
                    $params[$coluna] = "{$valor}";
                } else {
                    $str_where .= " {$coluna} = :{$coluna} ";
                    $params[$coluna] = "{$valor}";
                }
                // Enquanto existir um filtro, adiciona o operador AND
                if (next($filtros)) {
                    $str_where .= ' AND ';
                }
            }
        }

        if (!empty($buscas) && (count($buscas) > 0)) {
            // Caso exista um campo para busca, fecha os parênteses anterior
            // e adiciona mais um AND (, que conterá os parâmetros de busca (OR)
            if (!empty($str_where)) {
                $str_where .= ') AND (';
            } else {
                // Caso não tenha nenhum filtro anterior, adiciona o WHERE
                $str_where .= " WHERE (";
            }
            foreach ($buscas as $coluna => $valor) {
                $str_where .= " {$coluna} LIKE :{$coluna} ";
                $params[$coluna] = "%{$valor}%";

                // Enquanto existir uma busca, adiciona o operador OR
                if (next($buscas)) {
                    $str_where .= ' OR ';
                } else {
                    // Fecha o parênteses do OR
                    $str_where .= ') ';
                }
            }
        } else {
            // Fecha o parênteses dos filtros, caso tenha sido aberto
            if (!empty($str_where)) {
                $str_where .= ')';
            }
        }

        return [$str_where, $params];
    }
}
