<?php

namespace Uspdev\Replicado;

class Posgraduacao 
{
    public static function verifica($codpes, $codundclgi)
    {
        $cols = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $query = " SELECT {$cols} FROM LOCALIZAPESSOA WHERE codpes = {$codpes}"; 
        $result = DB::fetchAll($query);

        $return = false;
        foreach($result as $row)
        {
            if(trim($row['tipvin']) == 'ALUNOPOS' && trim($row['sitatl']) == 'A'  && trim($row['codundclg']) == $codundclgi) 
                $return = true;    
        }
        return $return;
    }

    public static function ativos($codundclgi)
    {
        $cols1 = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $cols2 = file_get_contents('replicado_queries/tables/pessoa.sql', true);
        $query = " SELECT {$cols1},{$cols2} FROM LOCALIZAPESSOA "; 
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) "; 
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ALUNOPOS' AND LOCALIZAPESSOA.codundclg = '{$codundclgi}' "; 
        $query .= " ORDER BY PESSOA.nompes ASC "; 
        $result = DB::fetchAll($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        return $result;
    }

    public static function ativosCsv($codundclgi)
    {
        $cols = ['codpes','nompes','codema','numcpf'];
        return Uteis::makeCsv($this->ativos($codundclgi),$cols);
    }
}
