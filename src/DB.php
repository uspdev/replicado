<?php

namespace Uspdev\Replicado;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;
use SplFileInfo;
use Throwable;
use Uspdev\Cache\Cache;

class DB
{
    /**
     * Instância do DB
     * @var Uspdev\Replicado\DB $db
     */
    protected static $db = null;

    /**
     * Instância do PDO
     * @var PDO $instance
     */
    protected $instance;

    # Variáveis de config obrigatórias
    protected $host;
    protected $port;
    protected $database;
    protected $username;
    protected $password;

    # Variáveis de config opcionais
    protected $usarCache;
    protected $debug;
    protected $pathLog;
    protected $sybase;

    public function __construct($config = [])
    {
        $this->setConfig($config);
    }

    private function __clone()
    {}

    /**
     * Cria e retorna uma instância de db
     *
     * Aplica novas configurações se passados em $config
     *
     * @param Array $config
     * @return Uspdev\Replicado\DB
     */
    public static function getDB($config = [])
    {
        if (SELF::$db) {
            SELF::$db->setConfig($config);
        } else {
            SELF::$db = new DB($config);
        }
        return SELF::$db;
    }

    /**
     * Testa funcionamento do replicado fazendo a conexão com o banco
     *
     * @return bool
     */
    public static function test($config = [])
    {
        $db = SELF::getDB($config);
        return $db->getInstance() ? true : false;
    }

    /**
     * Sobrescreve método fetch
     *
     * @param String $query
     * @param Array $param
     * @return Mixed
     */
    public static function fetch(string $query, array $param = [])
    {
        $db = SELF::getDB();
        if ($db->usarCache) {
            return (new Cache($db))->getCached('overrideFetch', ['fetch', $query, $param]);
        } else {
            return $db->overrideFetch('fetch', $query, $param);
        }
    }

    /**
     * Sobrescreve método fetchAll
     *
     * @param String $query
     * @param Array $param
     * @return Mixed
     */
    public static function fetchAll(string $query, array $param = [])
    {
        $db = SELF::getDB();
        if ($db->usarCache) {
            return (new Cache($db))->getCached('overrideFetch', ['fetchAll', $query, $param]);
        } else {
            return $db->overrideFetch('fetchAll', $query, $param);
        }
    }

    /**
     * Códigos do fetch e fetchAll sobrescritos
     *
     * Deve ser public senão dá erro no cache
     * Call to protected method Uspdev\Replicado\DB::overrideFetch() from context 'Uspdev\Cache\Cache'
     *
     * @param String $fetchType - fetch ou fetchAll
     * @param String $query Query a ser executada
     * @param Array $param Parâmetros de bind da query
     * @return Mixed Dados da query, pode ser coleção, dicionário, string, etc
     */
    public function overrideFetch(string $fetchType, string $query, array $param = [])
    {
        $stmt = $this->getInstance()->prepare($query);
        foreach ($param as $campo => $valor) {
            $valor = $this->sybase ? utf8_decode($valor) : $valor;
            $stmt->bindValue(":$campo", $valor);
        }
        try {
            $stmt->execute();
        } catch (\Throwable $t) {
            $this->log('Consulta', $t->getMessage());
            if ($this->debug) {
                die('Consulta do replicado: ' . $t->getMessage());
            } else {
                die('Erro na consulta do replicado!');
            }
        }

        $result = $stmt->$fetchType(PDO::FETCH_ASSOC);

        if (!empty($result) && $this->sybase) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
        }
        return $result;
    }

    /**
     * Retorna as configurações em uso
     *
     * @return Array
     */
    public function getConfig()
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => '**********',
            'usarCache' => $this->usarCache,
            'debug' => $this->debug,
            'pathLog' => $this->pathLog,
            'sybase' => $this->sybase,
        ];
    }

    /**
     * Aplica configuração do DB
     *
     * Pode ser passado por parâmetro (tem precedência) ou pegar pelo env.
     * Além dos parâmetros do env, pode ser passado ['reset'=>true] para
     * reverter todas as configs para padrão env
     *
     * TODO: Não foram tratados os configs de queries: CODUNDCLG e CODCUR. ****************
     *
     * @param Array $config
     * @return Void
     */
    public function setConfig(array $config = [])
    {
        if (isset($config['reset']) && $config['reset'] == true) {
            $this->host = $config['host'] ?? Uteis::env('REPLICADO_HOST');
            $this->port = $config['port'] ?? Uteis::env('REPLICADO_PORT');
            $this->database = $config['database'] ?? Uteis::env('REPLICADO_DATABASE');
            $this->username = $config['username'] ?? Uteis::env('REPLICADO_USERNAME');
            $this->password = $config['password'] ?? Uteis::env('REPLICADO_PASSWORD');

            $this->usarCache = $config['usarCache'] ?? Uteis::env('REPLICADO_USAR_CACHE', false);
            $this->debug = $config['debug'] ?? Uteis::env('REPLICADO_DEBUG', false);
            $this->pathLog = $config['pathLog'] ?? Uteis::env('REPLICADO_PATHLOG', '/tmp/replicado.log');
            $this->sybase = $config['sybase'] ?? Uteis::env('REPLICADO_SYBASE', true);

            $this->instance = null;
        } else {
            $this->host = isset($config['host']) ? $config['host'] : ($this->host ?: Uteis::env('REPLICADO_HOST'));
            $this->port = isset($config['port']) ? $config['port'] : ($this->port ?: Uteis::env('REPLICADO_PORT'));
            $this->database = isset($config['database']) ? $config['database'] : ($this->database ?: Uteis::env('REPLICADO_DATABASE'));
            $this->username = isset($config['username']) ? $config['username'] : ($this->username ?: Uteis::env('REPLICADO_USERNAME'));
            $this->password = isset($config['password']) ? $config['password'] : ($this->password ?: Uteis::env('REPLICADO_PASSWORD'));

            $this->usarCache = isset($config['usarCache']) ? $config['usarCache'] : ($this->usarCache ?: Uteis::env('REPLICADO_USAR_CACHE', false));
            $this->debug = isset($config['debug']) ? $config['debug'] : ($this->debug ?: Uteis::env('REPLICADO_DEBUG', false));
            $this->pathLog = isset($config['pathLog']) ? $config['pathLog'] : ($this->pathLog ?: Uteis::env('REPLICADO_PATHLOG', '/tmp/replicado.log'));
            $this->sybase = isset($config['sybase']) ? $config['sybase'] : ($this->sybase ?: Uteis::env('REPLICADO_SYBASE', true));
            // a lógica das linhas acima é igual a essa
            // if (isset($config['host'])) {
            //   $this->host = $config['host'];
            // } else {
            //   $this->host = $this->host ?: Uteis::env('REPLICADO_HOST');
            // }
        }

        if (empty($this->host) || empty($this->port) || empty($this->username) || empty($this->password)) {
            $this->log('Config', 'Configurações do replicado incompletas.');
            die('Configurações do replicado incompletas.');
        }
    }

    /**
     * Cria e retorna ima instância do pode
     *
     * @return PDO
     */
    protected function getInstance()
    {
        if (!$this->instance) {
            try {
                $dsn = "dblib:host={$this->host}:{$this->port};dbname={$this->database}";
                $this->instance = new PDO($dsn, $this->username, $this->password, [PDO::ATTR_TIMEOUT => 10]);
                $this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Throwable $t) {
                $this->log('Conexão', $t->getMessage());
                if ($this->debug) {
                    die("Conexão com o replicado: " . $t->getMessage());
                } else {
                    die('Erro na conexão com o replicado! Contate o suporte.');
                }
            }
        }
        return $this->instance;
    }

    /**
     * Grava em log uma mensagem
     *
     * @param String $channelName
     * @param String $message
     * @return void
     */
    protected function log(string $channelName, string $message)
    {
        $logger = new Logger($channelName);
        $logger->pushHandler(new StreamHandler($this->pathLog, Logger::DEBUG));
        $logger->error($message);
    }

    /**
     * Retorna array contendo string formatada do WHERE com os filtros/buscas e as colunas => valores no formato para 'bind'
     *
     * A $str_where pode ser colocada dentro de $query. Cuidado: ela vem iniciada pela string " WHERE ("
     * $params pode ser passado diretamente no fetch/fetchAll
     * 
     * TODO: Talvez esse método deveria estar em outro lugar
     *
     * 28/1/2022 - Adicionado $colunaSanitizada poara o caso de passar "tabela.coluna". $colunaSanitizada remove o ponto no $param
     *
     * @param array $filtros - campo_tabela => valor
     * @param array $buscas - campo_tabela => valor
     * @param array $tipos - campo_tabela => tipo (ex.: codpes => int)
     * @return array posição [0] => string WHERE, posição [1] = 'colunas' => valores
     */
    public static function criaFiltroBusca(array $filtros, array $buscas, array $tipos)
    {
        // Abre o parênteses dos filtros
        $str_where = "";
        $params = [];
        if (!empty($filtros) && (count($filtros) > 0)) {
            $str_where .= " WHERE (";
            foreach ($filtros as $coluna => $valor) {
                $colunaSanitizada = str_replace('.', '', $coluna);
                if (array_key_exists($coluna, $tipos)) {
                    $str_where .= " {$coluna} = CONVERT({$tipos[$coluna]}, :{$colunaSanitizada}) ";
                    $params[$colunaSanitizada] = $valor;
                } else {
                    $str_where .= " {$coluna} = :{$colunaSanitizada} ";
                    $params[$colunaSanitizada] = $valor;
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
                $colunaSanitizada = str_replace('.', '', $coluna);
                $str_where .= " {$coluna} LIKE :{$colunaSanitizada} ";
                $params[$colunaSanitizada] = "%{$valor}%";

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

    /**
     * Método auxiliar que ajuda carregar o conteúdo do arquivo contendo sql
     *
     * Arquivos de query estão na pasta resources/queries
     *
     * @param string $filename
     * @return String
     */
    public static function getQuery(string $filename)
    {
        $path = new SplFileInfo(__DIR__);
        $queries = $path->getRealPath();
        $queries .= DIRECTORY_SEPARATOR;
        $queries .= '..';
        $queries .= DIRECTORY_SEPARATOR;
        $queries .= 'resources';
        $queries .= DIRECTORY_SEPARATOR;
        $queries .= 'queries';
        return file_get_contents($queries . DIRECTORY_SEPARATOR . $filename);
    }
}
