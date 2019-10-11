<?php

use Uspdev\Replicado\Posgraduacao;

include_once __DIR__ . '/../credentials.php';

$codcur = empty($codcur) ? '' : $codcur;
$codare = empty($codare) ? '' : $codare;

echo "Método Posgraduacao::alunosPrograma(unidade=$unidade, cudcur=$codcur, codare=$codare) => ";

if (is_callable(['Uspdev\Replicado\Posgraduacao', 'alunosPrograma'], false, $callable_name)) {
    echo ' . ';
} else {
    //echo $callable_name;
    echo red(' método indefinido') . PHP_EOL; return;
}

$alunosPrograma = Posgraduacao::alunosPrograma($unidade, $codcur, $codare);
echo "\n";
echo "Alunos ativos no programa $codcur: " . count($alunosPrograma) . "\n";
print_r($alunosPrograma);exit;

foreach ($alunosPrograma as $codare => $alunosArea) {
    $i = 0;
    foreach ($alunosArea as $a) {
        echo ++$i . ", $codare, {$a['codpes']}, {$a['nompes']}, {$a['nivpgm']}, {$a['codema']} \n";
    }
}

