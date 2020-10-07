<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'disciplinasOferecimento';

$codare = isset($codare) ? $codare : null;
echo "Método Posgraduacao::$metodo(codare=$codare) => ";

testa_existe_metodo([$ns, $metodo]);

if (empty($codare)) {
    echo red('parâmetro obrigatório não fornecido') . PHP_EOL;
    return;
}

$res = Posgraduacao::$metodo($codare);
echo count($programas);
echo green(' OK') . PHP_EOL;

// vamos pegar o 1o para testar o oferecimento
$oferecimento = [
    [
        'pname' => 'sgldis',
        'pval' => $res[0]['sgldis'],
    ],
    [
        'pname' => 'numofe',
        'pval' => $res[0]['numofe'],
    ],
];
