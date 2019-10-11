<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Uspdev\Replicado\Posgraduacao;

define('UNIDADE', 18);

putenv('REPLICADO_HOST=143.107.182.9');
putenv('REPLICADO_PORT=1039');
putenv('REPLICADO_DATABASE=replicacao');
putenv('REPLICADO_USERNAME=masaki');
putenv('REPLICADO_PASSWORD=m9av6p3fajEEgW$y');
putenv('REPLICADO_PATHLOG=log.log');


$discipl = Posgraduacao::disciplina('SET5956');
print_r($discipl);