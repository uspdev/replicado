<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Uteis;

class Pessoa 
{
    private $conn;
    private $uteis;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->uteis = new Uteis;
    }

    public function dump($codpes)
    {
        $cols = file_get_contents('replicado_queries/tables/pessoa.sql', true);
        $query = " SELECT {$cols} FROM DBMAINT.PESSOA WHERE codpes = '{$codpes}'"; 
        $q = $this->conn->query($query);
        $result = $q->fetchAll()[0];
        $result = $this->uteis->utf8_converter($result);
        return $result;
    }

    public function cracha($codpes)
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

    public function telefones($codpes)
    {
        $cols1 = file_get_contents('replicado_queries/tables/telefpessoa.sql', true);
        $cols2 = file_get_contents('replicado_queries/tables/localidade.sql', true);
        $query = " SELECT {$cols1},{$cols2} FROM DBMAINT.TELEFPESSOA ";
        $query = " FULL OUTER JOIN LOCALIDADE ON TELEFPESSOA.codlocddd = LOCALIDADE.codloc ";
        $query .= " WHERE TELEFPESSOA.codpes = '{$codpes}'";
        $r = $this->db->query($query);
        $result = $r->fetchAll();
        /*
        $telefones= array();
        foreach($result as $row)
        {
            $email = trim($row['codema']);
            in_array($email,$emails) ?: array_push($emails,$email);
        }
         */
        return $result;
    }

}
