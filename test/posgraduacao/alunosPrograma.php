<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'alunosPrograma';

$codcur = empty($codcur) ? null : $codcur;
$codare = empty($codare) ? null : $codare;

echo "Método Posgraduacao::$metodo(unidade=$unidade, cudcur=$codcur, codare=$codare) => ";

testa_existe_metodo([$ns, $metodo]);

if (empty($codcur)) {
    echo red('parâmetro obrigatório não fornecido') . PHP_EOL;
    return;
}

$res = Posgraduacao::$metodo($unidade, $codcur, $codare);

if ($res) {
    echo count($res) . green(' OK') . PHP_EOL;
} else {
    echo red(' algo deu errado') . PHP_EOL;
}
