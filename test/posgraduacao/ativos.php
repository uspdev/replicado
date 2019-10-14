<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'ativos';

echo "Método Posgraduacao::$metodo(unidade=$unidade) => ";

testa_existe_metodo([$ns, $metodo]);

$res = Posgraduacao::$metodo($unidade);

echo count($res);
echo green(' OK') . PHP_EOL;

