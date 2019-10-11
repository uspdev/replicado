<?php

use Uspdev\Replicado\Posgraduacao;

include_once __DIR__ . '/../credentials.php';

$unidade = isset($unidade)?$unidade:null;

echo "Método Posgraduacao::programas(unidade=$unidade) => ";

if (is_callable(['Uspdev\Replicado\Posgraduacao', 'programas'], false, $callable_name)) {
    echo ' . ';
} else {
    echo $callable_name . ' indefinido' . PHP_EOL;
}

$programas = Posgraduacao::programas($unidade);
echo count($programas);
echo green(' OK') . PHP_EOL;

// foreach ($programas as $programa) {
//     $codcur = $programa['codcur'];
//     echo '  '.$programa['codcur'] . ' - ' . $programa['nomcur'] . PHP_EOL;
// }
// echo PHP_EOL;
