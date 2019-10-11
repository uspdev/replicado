<?php

use Uspdev\Replicado\Posgraduacao;

include_once __DIR__ . '/../credentials.php';

$codare = isset($codare) ? $codare : null;
echo "Método Posgraduacao::disciplinasOferecimento(codare=$codare) => ";

if (is_callable(['Uspdev\Replicado\Posgraduacao', 'disciplinasOferecimento'], false, $callable_name)) {
    echo ' . ';
} else {
    echo $callable_name . red(' indefinido') . PHP_EOL;
}

if (empty($codare)) {
    echo red('codare não definido') . PHP_EOL;return;
}
$res = Posgraduacao::disciplinasOferecimento($codare);
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

// print_r($res);
// exit;
