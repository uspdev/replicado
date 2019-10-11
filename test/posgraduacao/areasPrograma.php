<?php

use Uspdev\Replicado\Posgraduacao;

$metodo = 'areasProgramas';
$p_str = 'unidade=';
$params = [
    [
        'name' => 'unidade',
        'val' => $unidade,
    ],
];

include_once __DIR__ . '/../credentials.php';

// para mais de um parametro
if (isset($params)) {
    $p_str = '';
    unset($p_val);
    foreach ($params as $param) {
        $p_str .= $param['name'] . '=' . $param['val'] . ', ';
        $p_val[] = $param['val'];
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

// varifica se o método está definido
if (is_callable(['Uspdev\Replicado\Posgraduacao', $metodo], false, $callable_name)) {
    echo ' . ';
} else {
    //echo $callable_name;
    echo red(' método indefinido') . PHP_EOL;return;
}

// chama o método propriamente
$res = Posgraduacao::$metodo(...$p_val);

if ($res) {
    echo count($res) . green(' OK') . PHP_EOL;
} else {
    echo red(' algo deu errado') . PHP_EOL;
}
