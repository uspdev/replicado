<?php
namespace Meu\Lindo\App;

require_once __DIR__ . '/../vendor/autoload.php';
use Uspdev\Replicado\Posgraduacao;

define('UNIDADE', 18);

putenv('REPLICADO_HOST=143.107.182.9');
putenv('REPLICADO_PORT=1039');
putenv('REPLICADO_DATABASE=replicacao');
putenv('REPLICADO_USERNAME=masaki');
putenv('REPLICADO_PASSWORD=m9av6p3fajEEgW$y');
putenv('REPLICADO_PATHLOG=log.log');


// as quantidades de disciplinas de cada área foram coletadas manualmente 
// so Janus em 11/9/2019
$areas = [
    18134 => 42,
    18139 => 32,
    18140 => 0,
    18157 => 15,
    18156 => 25,
    18158 => 25,
    18143 => 26,
    18144 => 17,
    18137 => 0,
    18151 => 0,
    18133 => 0,
    18152 => 21,
    18153 => 20,
    18154 => 19,
    18155 => 12,
    18138 => 43,
    18161 => 23,
    18148 => 3,
    18149 => 2,
    18162 => 23,
    18135 => 0,
    18145 => 1,
    18150 => 1,
    18163 => 23,
    18164 => 14,
    18132 => 24,
    18160 => 14,

];
foreach ($areas as $area => $count) {
    $disciplinas = Posgraduacao::catalogoDisciplinas($area);
    if (count($disciplinas) == $count) {
        echo $area . ' - ok' .  PHP_EOL;
    } else {
        echo $area . ' - janus ' . $count .'=>'.count($disciplinas) . PHP_EOL;

    }

}
//print_r($disciplinas);
