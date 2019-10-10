<?php

use Uspdev\Replicado\Posgraduacao;

include_once __DIR__ . '/../credentials.php';

$codare = empty($codare) ? '' : $codare;

echo "Método Posgraduacao::catalogoDisciplinas(codare=$codare) => ";

if (is_callable(['Uspdev\Replicado\Posgraduacao', 'catalogoDisciplinas'], false, $callable_name)) {
    echo ' . ';
} else {
    //echo $callable_name;
    echo red(' método indefinido') . PHP_EOL; return;
}

$res = Posgraduacao::catalogoDisciplinas($codare);

echo count($res);
echo green(' OK') . PHP_EOL;

// vamos pegar a primeira disciplina do catálogo
$sgldis = $res[0]['sgldis'];

