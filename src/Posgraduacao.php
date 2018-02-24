<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Uteis;

class Posgraduacao 
{
    private $db;
    private $uteis;

    public function __construct($db)
    {
        $this->db = $db;
        $this->uteis = new Uteis;
    }

    public function verificaSeAtivo($codpes, $codundclgi)
    {
        $cols = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $query = " SELECT {$cols} FROM DBMAINT.LOCALIZAPESSOA WHERE codpes = '{$codpes}'"; 
        $q = $this->db->query($query);
        $result = $q->fetchAll();

        $return = false;
        foreach($result as $row)
        {
            if(trim($row['tipvin']) == 'ALUNOPOS' && trim($row['sitatl']) == 'A'  && trim($row['codundclg']) == $codundclg) 
                $return = true;    
        }
        return $return;
    }

}
