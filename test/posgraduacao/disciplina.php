<?php

use Uspdev\Replicado\Posgraduacao;

$metodo = 'disciplina';

include_once __DIR__ . '/../credentials.php';

$pname = 'sgldis';
$pval = empty($$pname) ? '' : $$pname;

echo "Método Posgraduacao::$metodo($pname=$pval) => ";

if (empty($sgldis)) {
    echo red(' sgldis indefinido').PHP_EOL;
    return;
}

if (is_callable(['Uspdev\Replicado\Posgraduacao', $metodo], false, $callable_name)) {
    echo ' . ';
} else {
    //echo $callable_name;
    echo red(' método indefinido') . PHP_EOL; return;
}

$res = Posgraduacao::$metodo($pval);

if ($res) {
    echo green('OK') . PHP_EOL;
} else {
    echo red(' algo deu errado') . PHP_EOL;
}

