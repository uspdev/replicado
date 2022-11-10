<?php

namespace Uspdev\Replicado;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Config
{

    /** Instância do Config */
    protected static $instance;

    /** Instância do logger */
    protected static $logger;

    # Variáveis de config
    public $host;
    public $port;
    public $database;
    public $username;
    public $password;
    public $codundclg;
    public $codundclgs;

    public $pathlog = '/tmp/replicado.log';
    public $usarCache = false;
    public $debug = false;
    public $sybase = true;

    protected $vars = ['host', 'port', 'database', 'username', 'password', 'pathlog', 'usarCache', 'debug', 'sybase', 'codundclg', 'codundclgs'];

    public function __construct()
    {
    }

    /**
     * Retorna uma instância do config existente ou cria uma nova
     *
     * @return \Uspdev\Replicado\Config
     */
    public static function getInstance($newConfig = [])
    {
        if (!SELF::$instance) {
            SELF::$instance = new Config();
            SELF::$instance->setConfig($newConfig);
        }
        return SELF::$instance;
    }

    /**
     * Retorna as configurações em uso
     *
     * @return Array
     */
    public static function getConfig()
    {
        $config = SELF::getInstance();
        foreach ($config->vars as $var) {
            $ret[$var] = $config->$var;
        }
        $ret['password'] = '**********'; //mascarando password

        return $ret;
    }

    /**
     * Aplica configuração do Config
     *
     * Pode ser passado por parâmetro (tem precedência) ou pegar pelo env.
     * Além dos parâmetros do env, pode ser passado
     * ['reset'=>true]
     * para reverter todas as configs para padrão env
     *
     * TODO: Ainda não foram tratados os configs de queries: CODUNDCLG e CODUNDCLGS. ****************
     *
     * @param Array $config (default=[])
     * @return Array Retorna configurações em uso
     */
    public static function setConfig(array $newConfig = [])
    {
        if (isset($newConfig['reset']) && $newConfig['reset'] == true) {
            SELF::$config = new Config();
        }
        $config = SELF::getInstance();

        foreach ($config->vars as $var) {
            // var=usarCache -> varSnake=usar_cache
            $varSnake = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $var)), '_');

            if (isset($newConfig[$var])) {
                $config->$var = $newConfig[$var];
            } else {
                $config->$var = Uteis::env('REPLICADO_' . strtoupper($varSnake));
            }
        }

        if (empty($config->host) || empty($config->port) || empty($config->username) || empty($config->password)) {
            $config->log('Config', 'Configurações do replicado incompletas.');
            die('Configurações do replicado incompletas.');
        }

        return $config->getConfig();
    }

    /**
     * Grava em log uma mensagem
     *
     * @param String $channelName
     * @param String $message
     * @return void
     */
    public function log(string $channelName, string $message)
    {
        if (!isset(SELF::$logger)) {
            SELF::$logger = new Logger($channelName);
            SELF::$logger->pushHandler(new StreamHandler($this->pathlog, Logger::DEBUG));
        }
        SELF::$logger->error($message);
    }

}
