<?php

use Uspdev\Replicado\Posgraduacao;

$metodo = 'ativos';

include_once __DIR__ . '/../credentials.php';


echo "Método Posgraduacao::$metodo(unidade=$unidade) => ";

if (is_callable(['Uspdev\Replicado\Posgraduacao', $metodo], false, $callable_name)) {
    echo ' . ';
} else {
    //echo $callable_name;
    echo red(' método indefinido') . PHP_EOL; return;
}

$res = Posgraduacao::$metodo($unidade);

echo count($res);
echo green(' OK') . PHP_EOL;

