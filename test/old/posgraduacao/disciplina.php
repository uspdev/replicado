<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'disciplina';

$pname = 'sgldis';
$pval = empty($$pname) ? '' : $$pname;

echo "Método Posgraduacao::$metodo($pname=$pval) => ";

testa_existe_metodo([$ns, $metodo]);

if (empty($sgldis)) {
    echo red(' sgldis indefinido').PHP_EOL;
    return;
}


$res = Posgraduacao::$metodo($pval);

if ($res) {
    echo green('OK') . PHP_EOL;
} else {
    echo red(' algo deu errado') . PHP_EOL;
}

