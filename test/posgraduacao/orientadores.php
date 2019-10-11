<?php

use Uspdev\Replicado\Posgraduacao;

include_once __DIR__ . '/../credentials.php';

$codare = empty($codare) ? '' : $codare;

echo "Método Posgraduacao::orientadores(codare=$codare) => ";

if (is_callable(['Uspdev\Replicado\Posgraduacao', 'orientadores'], false, $callable_name)) {
    echo ' . ';
} else {
    //echo $callable_name;
    echo red(' método indefinido') . PHP_EOL; return;
}

$res = Posgraduacao::orientadores($codare);

echo count($res);
echo green(' OK') . PHP_EOL;

