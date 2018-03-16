<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Uteis;

class Posgraduacao 
{
    private $conn;
    private $uteis;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->uteis = new Uteis;
    }

    public function verifica($codpes, $codundclgi)
    {
        $cols = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $query = " SELECT {$cols} FROM LOCALIZAPESSOA WHERE codpes = '{$codpes}'"; 
        $q = $this->coon->query($query);
        $result = $q->fetchAll();

        $return = false;
        foreach($result as $row)
        {
            if(trim($row['tipvin']) == 'ALUNOPOS' && trim($row['sitatl']) == 'A'  && trim($row['codundclg']) == $codundclg) 
                $return = true;    
        }
        return $return;
    }

    public function ativos($codundclgi)
    {
        $cols1 = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $cols2 = file_get_contents('replicado_queries/tables/pessoa.sql', true);
        $query = " SELECT {$cols1},{$cols2} FROM LOCALIZAPESSOA "; 
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) "; 
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ALUNOPOS' AND LOCALIZAPESSOA.codundclg = '{$codundclgi}' "; 
        $query .= " ORDER BY PESSOA.nompes ASC "; 
        $q = $this->conn->query($query);
        $result = $q->fetchAll();
        $result = $this->uteis->utf8_converter($result);
        $result = $this->uteis->trim_recursivo($result);

        return $result;
    }

    public function ativosCsv($codundclgi)
    {
        $cols = ['codpes','nompes','codema','numcpf'];
        return $this->uteis->makeCsv($this->ativos($codundclgi),$cols);
    }
}
