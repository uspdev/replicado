<?php
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
putenv('REPLICADO_CODUNDCLG=8');

\$unidade = 00;
\$codcur = 18005; // código do curso de PG
\$codare = 18134; // código de área da PG
\$codpes = 1575309; // nro USP para testar se é aluno da PG ou não

";
}

function credentials($argv)
{
    if (empty($argv[1])) {
        echo red('Erro: ') . 'Voce deve passar como parâmetro o arquivo contendo as credenciais!' . PHP_EOL;
        printHelp();
        exit;
    }
    
    $credentials = $argv[1];

    if (is_file($credentials)) {
        return $credentials;
    } else {
        echo red('Erro: ') . 'Arquivo ' . $credentials . ' não encontrado!' . PHP_EOL;
        printHelp();
        exit;
    }
}

function testa_existe_metodo($metodo)
{
    if (is_callable($metodo, false, $callable_name)) {
        echo ' . ';
    } else {
        //echo $callable_name;
        echo red(' método indefinido') . PHP_EOL;return;
    }
}
