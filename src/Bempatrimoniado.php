<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Uteis;

class Bempatrimoniado 
{
    private $db;
    private $uteis;

    public function __construct($db)
    {
        $this->db = $db;
        $this->uteis = new Uteis;
    }

    public function dump($numpat)
    {

       $cols = file_get_contents('replicado_queries/tables/bempatrimoniado.sql', true);
        $query = " SELECT {$cols} FROM BEMPATRIMONIADO WHERE numpat = '{$numpar}'"; 
        $q = $this->db->query($query);
        $result = $q->fetchAll()[0];
        $result = $this->uteis->utf8_converter($result);
        $result = $this->uteis->trim_recursivo($result);
        return $result;
    }

/* FROM BEMPATRIMONIADO

     WHERE (BEMPATRIMONIADO.coditmmat = '12513' OR BEMPATRIMONIADO.coditmmat = '51110' OR BEMPATRIMONIADO.coditmmat = '354384' OR BEMPATRIMONIADO.coditmmat = '354341' OR BEMPATRIMONIADO.coditmmat = '9300' OR BEMPATRIMONIADO.coditmmat = '162213' OR BEMPATRIMONIADO.coditmmat = '57100' OR BEMPATRIMONIADO.coditmmat = '45624')
     AND BEMPATRIMONIADO.stabem = 'Ativo'
*/
}





