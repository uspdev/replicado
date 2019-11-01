<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'egressosArea';

$codcur = empty($codcur) ? null : $codcur;
$codare = empty($codare) ? null : $codare;

echo "Método Posgraduacao::$metodo(codare=$codare) => ";

testa_existe_metodo([$ns, $metodo]);

if (empty($codcur)) {
    echo red('parâmetro obrigatório não fornecido') . PHP_EOL;
    return;
}

$res = Posgraduacao::$metodo($codare);

if ($res) {
    echo count($res) . green(' OK') . PHP_EOL;
} else {
    echo red(' algo deu errado') . PHP_EOL;
}
