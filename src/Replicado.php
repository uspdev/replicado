<?php

namespace Uspdev\Replicado;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Uspdev\Cache\Cache;

class Replicado
{

    /** Instância da classe singleton */
    private static $instance;

    /** Instância do logger */
    private static $logger;

    /**Instância do cache */
    private $cache;

    # Variáveis de config
    public $host; //obrigatório
    public $port; //obrigatório
    public $database;
    public $username; //obrigatório
    public $password; //obrigatório
    public $codundclg;
    public $codundclgs;

    public $sybase = true;
    public $usarCache = false;
    public $cacheExpiry = 4 * 60 * 60;
    public $cacheSmall = 32;

    public $pathlog = '/tmp/replicado.log';
    public $debug = false;
    public $debugLevel = 1; // 1: somente erros, 2: log de queries

    /** Variaveis que podem ser atualizadas pelo setConfig() */
    protected $vars = [
        'host', 'port', 'database', 'username', 'password',
        'codundclg', 'codundclgs', 'sybase', 'usarCache', 'cacheExpiry', 'cacheSmall',
        'pathlog', 'debug', 'debugLevel'];

    private function __construct()
    {}

    private function __clone()
    {}

    /**
     * Retorna uma instância do config existente ou cria uma nova
     *
     * @return \Uspdev\Replicado\Replicado
     */
    public static function getInstance($newConfig = [])
    {
        if (!SELF::$instance) {
            SELF::$instance = new Self;
            SELF::$instance->setConfig($newConfig);
        }
        return SELF::$instance;
    }

    /**
     * Retorna uma instância do cache
     *
     * Deve ser chamado depois de configurado o replicado
     */
    public function getCacheInstance($classToBeCached = null)
    {
        if (!$this->cache) {
            $this->cache = new Cache($classToBeCached);
            $this->setCacheConfig();
        }
        return $this->cache;
    }

    /**
     * Atualiza as configurações do cache
     *
     * Se $cacheExpiry ou $cacheSmall forem negativos deixam valor padrão
     *
     * @return void;
     */
    public function setCacheConfig()
    {
        if ($this->cache) {
            if ($this->cacheExpiry >= 0) {
                $this->cache->expiry = $this->cacheExpiry;
            }
            if ($this->cacheSmall >= 0) {
                $this->cache->small = $this->cacheSmall;
            }
            $this->cache->disable = false;
        }
    }

    /**
     * Retorna as configurações em uso
     *
     * Se não especificado $var retorna um array com todas as variáveis
     * Se retornar array, 'password vem mascarado'. Para obtê-lo use $var='password'
     *
     * @param $var Variável a ser retornada
     * @return Array|String
     */
    public static function getConfig($var = null)
    {
        $config = SELF::getInstance();
        if ($var) {
            return $config->$var;
        }
        foreach ($config->vars as $var) {
            $ret[$var] = $config->$var;
        }
        $ret['password'] = '**********'; //mascarando password

        return $ret;
    }

    /**
     * Aplica configuração do Replicado
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
            $newConfig = [];
        }
        $config = SELF::getInstance($newConfig);

        foreach ($config->vars as $var) {
            if (isset($newConfig[$var])) {
                $config->$var = $newConfig[$var];
            } else {
                // var=usarCache -> varSnake=USAR_CACHE
                $varSnake = ltrim(strtoupper(preg_replace('/[A-Z]/', '_$0', $var)), '_');
                $config->$var = Uteis::env('REPLICADO_' . $varSnake, $config->$var);
            }
        }

        // atualiza config do cache
        if ($config->usarCache) {
            $config->setCacheConfig();
        }

        if (empty($config->host) || empty($config->port) || empty($config->username) || empty($config->password)) {
            $config->log('Config', 'Configurações do replicado incompletas.');
            // die('Configurações do replicado incompletas.');
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
    public function log(string $channelName, string $message, string $level = 'error')
    {
        if (!isset(SELF::$logger)) {
            SELF::$logger = new Logger($channelName);
            SELF::$logger->pushHandler(new StreamHandler($this->pathlog, Logger::DEBUG));
        }
        SELF::$logger->$level($message);
    }

}
