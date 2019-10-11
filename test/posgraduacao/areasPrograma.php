<?php

use Uspdev\Replicado\Posgraduacao;

$ns = 'Uspdev\Replicado\Posgraduacao';
$metodo = 'areasProgramas';

$param_string = 'unidade=';
$params = [
    [
        'name' => 'unidade',
        'val' => $unidade,
    ],
];

// para mais de um parametro
if (isset($params)) {
    $param_string = '';
    unset($param_values);

    foreach ($params as $param) {
        $param_string .= $param['name'] . '=' . $param['val'] . ', ';
        $param_values[] = $param['val'];
    }

    $param_string = substr($param_string, 0, -2);
} else {
    $params = null;
}

echo "Método Posgraduacao::$metodo($param_string) => ";

if (empty($params)) {
    echo red(' faltando parametro obrigatório') . PHP_EOL;
    return;
}

testa_existe_metodo([$ns, $metodo]);

// chama o método propriamente
$res = Posgraduacao::$metodo(...$param_values);

if ($res) {
    echo count($res) . green(' OK') . PHP_EOL;
} else {
    echo red(' algo deu errado') . PHP_EOL;
}
