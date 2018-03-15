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
        $query = " SELECT {$cols} FROM DBMAINT.BEMPATRIMONIADO WHERE numpat = '{$numpar}'"; 
        $q = $this->db->query($query);
        $result = $q->fetchAll()[0];
        $result = $this->uteis->utf8_converter($result);
        return $result;
    }

    public function cracha($codpes)
     /*
     *FROM DBMAINT.BEMPATRIMONIADO

     WHERE (BEMPATRIMONIADO.coditmmat = '12513' OR BEMPATRIMONIADO.coditmmat = '51110' OR BEMPATRIMONIADO.coditmmat = '354384' OR BEMPATRIMONIADO.coditmmat = '354341' OR BEMPATRIMONIADO.coditmmat = '9300' OR BEMPATRIMONIADO.coditmmat = '162213' OR BEMPATRIMONIADO.coditmmat = '57100' OR BEMPATRIMONIADO.coditmmat = '45624')
     AND BEMPATRIMONIADO.stabem = 'Ativo'
     */
    {
        $cols = file_get_contents('replicado_queries/tables/catr_cracha.sql', true);
        $query = " SELECT {$cols} FROM DBMAINT.CATR_CRACHA WHERE codpescra = '{$codpes}'"; 
        $q = $this->db->query($query);
        $result = $q->fetchAll()[0];
        $result = $this->uteis->utf8_converter($result);
        return $result;
    }

    public function emails($codpes)
    {
        $cols = file_get_contents('replicado_queries/tables/emailpessoa.sql', true);
        $query = " SELECT {$cols} FROM DBMAINT.EMAILPESSOA WHERE codpes = '{$codpes}'";
        $r = $this->db->query($query);
        $result = $r->fetchAll();
        $emails= array();
        foreach($result as $row)
        {
            $email = trim($row['codema']);
            in_array($email,$emails) ?: array_push($emails,$email);
        }
        return $emails;
    }

    public function email($codpes)
    {
        $cols = file_get_contents('replicado_queries/tables/emailpessoa.sql', true);
        $query = " SELECT {$cols} FROM DBMAINT.EMAILPESSOA WHERE codpes = '{$codpes}'";
        $r = $this->db->query($query);
        $result = $r->fetchAll();
        foreach($result as $row)
        {
            if (trim($row['stamtr'])=='S')
                return $row['codema'];
        }
    }

    public function emailusp($codpes)
    {
        $cols = file_get_contents('replicado_queries/tables/emailpessoa.sql', true);
        $query = " SELECT {$cols} FROM DBMAINT.EMAILPESSOA WHERE codpes = '{$codpes}'";
        $r = $this->db->query($query);
        $result = $r->fetchAll();
        foreach($result as $row)
        {
            if (trim($row['stausp'])=='S')
                return $row['codema'];
        }
    }
}
