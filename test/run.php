<?php
ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

function red($string)
{
    return "\033[31m" . $string . "\033[0m";
}

function green($string)
{
    return "\033[32m" . $string . "\033[0m";
}

function blue($string)
{
    return "\033[34m" . $string . "\033[0m";
}

function printHelp()
{
    echo "Crie um arquivo chamado " . blue('credentials.php') . " com o conteúdo abaixo e chame " . blue('php test/run.php credentials.php') . PHP_EOL;
    echo "
putenv('REPLICADO_HOST=000.000.000.000');
putenv('REPLICADO_PORT=1039');
putenv('REPLICADO_DATABASE=replicacao');
putenv('REPLICADO_USERNAME=user');
putenv('REPLICADO_PASSWORD=password');
putenv('REPLICADO_PATHLOG='.__DIR__.'/log.log');

\$unidade = 00;
\$codcur = 18005; // código do curso de PG
\$codare = 18134; // código de área da PG
\$codpes = 1575309; // nro USP para testar se é aluno da PG ou não

";
}

if (php_sapi_name() != 'cli') {
    echo 'Os testes devem ser rodados em linha de comando!' . PHP_EOL;
    exit;
}

if (empty($argv[1])) {
    echo red('Erro: ') . 'Voce deve passar como parâmetro o arquivo contendo as credenciais!' . PHP_EOL;
    printHelp();
    exit;
}
$credentials = $argv[1];

if (is_file($credentials)) {
    include_once $credentials;
} else {
    echo red('Erro: ') . 'Arquivo ' . $credentials . ' não encontrado!' . PHP_EOL;
    printHelp();
    exit;
}

if (empty($unidade)) {
    echo red('Unidade não definido! Impossível continuar.' . PHP_EOL);
    exit;
}

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    echo red('Erro: ') . 'Você rodou composer install?' . PHP_EOL;
    exit;
}

include_once __DIR__ . '/posgraduacao/verifica.php';
include_once __DIR__ . '/posgraduacao/ativos.php';
include_once __DIR__ . '/posgraduacao/programas.php';
include_once __DIR__ . '/posgraduacao/orientadores.php';
include_once __DIR__ . '/posgraduacao/catalogoDisciplinas.php';
include_once __DIR__ . '/posgraduacao/disciplina.php';
include_once __DIR__ . '/posgraduacao/disciplinasOferecimento.php';
include_once __DIR__ . '/posgraduacao/oferecimento.php';
include_once __DIR__ . '/posgraduacao/areasPrograma.php';
include_once __DIR__ . '/posgraduacao/alunosPrograma.php';
