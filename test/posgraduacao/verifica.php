<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'verifica';

$codpes = empty($codpes) ? '' : $codpes;

echo "Método Posgraduacao::$metodo(codpes=$codpes, unidade=$unidade) => ";

testa_existe_metodo([$ns, $metodo]);

if (empty($codpes)) {
    echo red(' codpes indefinido') . PHP_EOL;
    return;
}

$res = Posgraduacao::$metodo($codpes, $unidade);

if ($res) {
    echo green('sim OK') . PHP_EOL;
} else {
    echo green('nao OK') . PHP_EOL;
}
