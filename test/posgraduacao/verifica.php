<?php

use Uspdev\Replicado\Posgraduacao;

$metodo = 'verifica';

include_once __DIR__ . '/../credentials.php';

$codpes = empty($codpes) ? '' : $codpes;

echo "Método Posgraduacao::$metodo(codpes=$codpes, unidade=$unidade) => ";

if (empty($codpes)) {
    echo red(' codpes indefinido').PHP_EOL;
    return;
}

if (is_callable(['Uspdev\Replicado\Posgraduacao', $metodo], false, $callable_name)) {
    echo ' . ';
} else {
    //echo $callable_name;
    echo red(' método indefinido') . PHP_EOL; return;
}

$res = Posgraduacao::$metodo($codpes, $unidade);

if ($res) {
    echo green('sim OK') . PHP_EOL;
} else {
    echo green('nao OK') . PHP_EOL;
}

