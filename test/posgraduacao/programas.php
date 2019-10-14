<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'programas';

$unidade = isset($unidade)?$unidade:null;

echo "Método Posgraduacao::$metodo(unidade=$unidade) => ";

testa_existe_metodo([$ns, $metodo]);

$programas = Posgraduacao::$metodo($unidade);
echo count($programas);
echo green(' OK') . PHP_EOL;

// foreach ($programas as $programa) {
//     $codcur = $programa['codcur'];
//     echo '  '.$programa['codcur'] . ' - ' . $programa['nomcur'] . PHP_EOL;
// }
// echo PHP_EOL;
