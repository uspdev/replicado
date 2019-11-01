<?php
ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

require_once __DIR__.'/functions.php';


if (php_sapi_name() != 'cli') {
    echo 'Os testes devem ser rodados em linha de comando!' . PHP_EOL;
    exit;
}

include_once credentials($argv);

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
include_once __DIR__ . '/posgraduacao/egressosArea.php';
