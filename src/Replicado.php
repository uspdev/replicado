<?php

namespace Uspdev\Replicado;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Uspdev\Replicado\Cache;
use SplFileInfo;

class Replicado
{

    /** Instância da classe singleton */
    private static $instance;

    /** Instância do logger */
    private static $logger;

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
    public $cachePrefix = 'replicado:';

    public $pathlog = '/tmp/replicado.log';
    public $debug = false;
    public $debugLevel = 1; // 1: somente erros, 2: log de queries
    public $fake = false;

    /** Variaveis que podem ser atualizadas pelo setConfig() */
    protected $vars = [
        'host',
        'port',
        'database',
        'username',
        'password',
        'codundclg',
        'codundclgs',
        'sybase',
        'usarCache',
        'cacheExpiry',
        'cacheSmall',
        'cachePrefix',
        'pathlog',
        'debug',
        'debugLevel',
        'fake'
    ];

    private function __construct() {}

    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }


    /**
     * Retorna uma instância do config existente ou cria uma nova
     *
     * @return \Uspdev\Replicado\Replicado
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
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
        $config = self::getInstance();

        foreach ($config->vars as $var) {
            if (isset($newConfig[$var])) {
                $config->$var = $newConfig[$var];
            } else {
                // var=usarCache -> varSnake=USAR_CACHE
                $varSnake = ltrim(strtoupper(preg_replace('/[A-Z]/', '_$0', $var)), '_');
                $config->$var = Uteis::env('REPLICADO_' . $varSnake, $config->$var);
            }
        }

        if ($config->usarCache) {
            Cache::config(
                $config->cacheExpiry,
                $config->cacheSmall,
                $config->cachePrefix
            );
        }

        if (empty($config->host) || empty($config->port) || empty($config->username) || empty($config->password)) {
            $config->log('Config', 'Configurações do replicado incompletas.');
            // die('Configurações do replicado incompletas.');
        }

        return $config->getConfig();
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
        $config = self::getInstance();
        if ($var) {
            return $config->$var;
        }
        $ret = [];
        foreach ($config->vars as $var) {
            $ret[$var] = $config->$var;
        }
        $ret['password'] = '**********'; //mascarando password

        return $ret;
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
        if (!isset(self::$logger)) {
            self::$logger = new Logger('Replicado');
            self::$logger->pushHandler(new StreamHandler($this->pathlog, Logger::DEBUG));
        }
        self::$logger->$level("[$channelName] $message");
    }

    /**
     * Retorna a estrutura de dados fixa para finalidades de testes
     *
     */
    public static function getFake($name)
    {
        $name = str_replace('Uspdev\\Replicado\\Base', '', $name);
        $path = new SplFileInfo(__DIR__);
        $path = $path->getRealPath();
        $path .= DIRECTORY_SEPARATOR;
        $path .= '..';
        $path .= DIRECTORY_SEPARATOR;
        $path .= 'resources';
        $path .= DIRECTORY_SEPARATOR;
        $path .= 'fake';

        $file = $path . DIRECTORY_SEPARATOR . $name . '.json';

        if (is_readable($file)) {
            $json = file_get_contents($file);
            return json_decode($json, true);
        }
        return 'Não há dados fake criado para esse método ainda.';
    }
}
