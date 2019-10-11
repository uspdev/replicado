<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'oferecimento';

$p_str = 'sgldis=, numofe=';

// para mais de um parametro
if (isset($oferecimento)) {
    $params = $oferecimento;
    $p_str = '';
    foreach ($params as $param) {
        $p_str .= $param['pname'] . '=' . $param['pval'] . ', ';
        $p_val[] = $param['pval'];
    }
    $p_str = substr($p_str, 0, -2);
} else {
    $params = null;
}

echo "Método Posgraduacao::$metodo($p_str) => ";

testa_existe_metodo([$ns, $metodo]);

if (empty($params)) {
    echo red(' faltando parametro obrigatório') . PHP_EOL;
    return;
}

$res = Posgraduacao::$metodo(...$p_val);

if ($res) {
    echo green('OK') . PHP_EOL;
} else {
    echo red(' algo deu errado') . PHP_EOL;
}
