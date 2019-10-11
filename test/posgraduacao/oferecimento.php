<?php

use Uspdev\Replicado\Posgraduacao;

$metodo = 'oferecimento';
$p_str = 'sgldis=, numofe=';

include_once __DIR__ . '/../credentials.php';

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
    $param = null;
}

echo "Método Posgraduacao::$metodo($p_str) => ";

if (empty($params)) {
    echo red(' faltando parametro obrigatório') . PHP_EOL;
    return;
}

if (is_callable(['Uspdev\Replicado\Posgraduacao', $metodo], false, $callable_name)) {
    echo ' . ';
} else {
    //echo $callable_name;
    echo red(' método indefinido') . PHP_EOL;return;
}

$res = Posgraduacao::$metodo(...$p_val);

if ($res) {
    echo green('OK') . PHP_EOL;
} else {
    echo red(' algo deu errado') . PHP_EOL;
}
