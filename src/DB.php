<?php

namespace Uspdev\Replicado;

use PDO;
use SplFileInfo;
use Throwable;
use Uspdev\Cache\Cache;
use Uspdev\Replicado\Replicado as Config;

class DB
{
    /**
     * Instância do PDO
     * @var PDO $instance
     */
    private static $instance;

    private function __construct()
    {}
    private function __clone()
    {}

    /**
     * Retorna uma instância do pdo - cria ou reaproveita se for o caso
     *
     * @return PDO
     */
    protected static function getInstance()
    {
        $config = Config::getInstance();
        if (!SELF::$instance) {
            try {
                $dsn = "dblib:host={$config->host}:{$config->port};dbname={$config->database}";
                // SELF::$instance = new PDO($dsn, $config->username, $config->password, [PDO::ATTR_TIMEOUT => 10]);
                SELF::$instance = new PDO($dsn, $config->username, $config->password);
                SELF::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Throwable $t) {
                $config->log('Erro na conexão ', $t->getMessage());
                if ($config->debug) {
                    die('Erro na conexão com o replicado: ' . $t->getMessage());
                } else {
                    die('Erro na conexão com o replicado! Contate o suporte.');
                }
            }
        }
        return SELF::$instance;
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
        $config = Config::getInstance();
        if ($config->usarCache) {
            $cache = $config->getCacheInstance();
            return $cache->getCached('Uspdev\Replicado\DB::overrideFetch', ['fetch', $query, $param]);
        } else {
            return SELF::overrideFetch('fetch', $query, $param);
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
        $config = Config::getInstance();
        if ($config->usarCache) {
            $cache = $config->getCacheInstance();
            return $cache->getCached('Uspdev\Replicado\DB::overrideFetch', ['fetchAll', $query, $param]);
        } else {
            return SELF::overrideFetch('fetchAll', $query, $param);
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
    public static function overrideFetch(string $fetchType, string $query, array $param = [])
    {
        $config = Config::getInstance();
        $query = SELF::automaticReplaces($query);
        $stmt = SELF::getInstance()->prepare($query);
        foreach ($param as $campo => $valor) {
            $valor = $config->sybase ? utf8_decode($valor) : $valor;
            $stmt->bindValue(":$campo", $valor);
            if ($config->debugLevel >= 2) {
                $queryLog = str_replace(":$campo", $valor, $queryLog ?? $query);
            }
        }
        if ($config->debugLevel >= 2) {
            // remove comentários sql
            // https://stackoverflow.com/questions/9690448/regular-expression-to-remove-comments-from-sql-statement
            $queryLog = preg_replace('@(--[^\r\n]*)|(\#[^\r\n]*)|(/\*[\w\W]*?(?=\*/)\*/)@ms', '', $queryLog ?? $query);
            // remove espaços em excesso
            $queryLog = preg_replace('/\s+/', ' ', $queryLog);
            // pega a classe::método que chamou - procura nos 5 ultimos
            $fn = '';
            foreach (debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT, 5) as $bt) {
                if (!in_array($bt['function'], ['overrideFetch', 'getRaw', 'getCached', 'fetchAll', 'fetch'])) {
                    $fn = $bt['class'] . $bt['type'] . $bt['function'];
                    break;
                }
            }
            $config->log('debug:2 ', $fn . ', ' . $queryLog, 'info');
        }
        try {
            $stmt->execute();
        } catch (Throwable $t) {
            if ($config->debugLevel >= 1) {
                $config->log('Erro na consulta ', $t->getMessage());
            }
            if ($config->debug) {
                die('Erro na consulta do replicado: ' . $t->getMessage());
            } else {
                die('Erro na consulta do replicado! Contate o suporte.');
            }
        }

        $result = $stmt->$fetchType(PDO::FETCH_ASSOC);

        if (!empty($result) && $config->sybase) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
        }
        return $result;
    }

    /**
     * Testa funcionamento do replicado fazendo a conexão com o banco
     *
     * @return bool
     */
    public static function test($config = [])
    {
        $config = Config::getInstance($config);
        return SELF::getInstance() ? true : false;
    }

    /**
     * Retorna array contendo string formatada do WHERE com os filtros/buscas e as colunas => valores no formato para 'bind'
     *
     * A $str_where pode ser colocada dentro de $query. Cuidado: ela vem iniciada pela string " WHERE ("
     * $params pode ser passado diretamente no fetch/fetchAll
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
     * Função auxiliar que ajuda carregar o arquivo sql e realizar substituições
     *
     * Opcionalmente pode-se passar uma coleção tipo ['replace' => 'valor']
     * para se realizar a substituição. Vai substituir '__replace__' por 'valor'.
     * Se ['--replace--' => 'valor'], vai substituir 'replace' por 'valor'
     *
     * Caso não seja passado ['codundclgs' => 'valor'], o método pegará automaticamente
     * do env se necessário
     *
     * @param String $filename
     * @param Array $repĺaces (default=[])
     * @return String
     * @author Masakik, Fernando G. Moura, modificado em 28/10/2022
     * @author Masakik, modificado em 5/5/2023, incluindo replace de comentário
     * @author Masakik, 1/2/2024, revertendo replaces que foi removido indevidamente #566
     */
    public static function getQuery($filename, array $replaces = [])
    {
        $path = new SplFileInfo(__DIR__);
        $queries = $path->getRealPath();
        $queries .= DIRECTORY_SEPARATOR;
        $queries .= '..';
        $queries .= DIRECTORY_SEPARATOR;
        $queries .= 'resources';
        $queries .= DIRECTORY_SEPARATOR;
        $queries .= 'queries';
        $query = file_get_contents($queries . DIRECTORY_SEPARATOR . $filename);

        foreach ($replaces as $key => $val) {
            if (str_starts_with($key, '--') || str_starts_with($key, '__')) {
                $query = str_replace($key, $val, $query); // replace de comentário
            } else {
                $query = str_replace("__{$key}__", $val, $query);
            }
        }

        return $query;
    }

    /**
     * Replaces automáticos de variáveis do replicado
     *
     * Vai substituir __codundclg__ e __codundclgs__ pelo conteúdo da variável no config.
     *
     * @param String $query
     * @return String
     */
    public static function automaticReplaces($query)
    {
        $config = Config::getInstance();
        if (str_contains($query, '__codundclgs__')) {
            $codundclgs = $config->codundclgs;
            $codundclgs = $codundclgs ?: $config->codundclg;
            $query = str_replace('__codundclgs__', $codundclgs, $query);
        }
        if (str_contains($query, '__codundclg__')) {
            $query = str_replace('__codundclg__', $config->codundclg, $query);
        }

        return $query;
    }
}
