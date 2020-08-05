<?php

use Uspdev\Replicado\Pessoa;

$ns = 'Uspdev\Replicado\Pessoa';
$metodo = 'listarDocentes';

$codset = empty($codset) ? false : $codset;

echo "Método Pessoa::$metodo(unidade=$unidade, codset=$codset) => ";

testa_existe_metodo([$ns, $metodo]);

$res = Pessoa::$metodo($codset);

if ($res) {
    echo count($res) . green(' OK') . PHP_EOL;
} else {
    echo red(' algo deu errado') . PHP_EOL;
}
