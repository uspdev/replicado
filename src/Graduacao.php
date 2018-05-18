<?php

namespace Uspdev\Replicado;

class Graduacao 
{
    public static function verifica($codpes, $codundclgi)
    {
        $cols = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $query = " SELECT {$cols} FROM LOCALIZAPESSOA WHERE codpes = {$codpes}"; 
        $result = DB::fetchAll($query);
        
        $return = false;
        foreach($result as $row)
        {
            if(trim($row['tipvin']) == 'ALUNOGR' && 
               trim($row['sitatl']) == 'A'  && 
               trim($row['codundclg']) == $codundclgi) 
               $return = true;
        }
        return $return;
    }

    public static function ativos($codundclgi, $parteNome = null)
    {
        $cols1 = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $cols2 = file_get_contents('replicado_queries/tables/pessoa.sql', true);
        $query = " SELECT {$cols1},{$cols2},UPPER(PESSOA.nompes) as nompes_upper FROM LOCALIZAPESSOA ";

        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) ";
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ALUNOGR' AND LOCALIZAPESSOA.codundclg = '{$codundclgi}' ";

        if (!is_null($parteNome)) {
            $parteNome = trim(utf8_decode(Uteis::removeAcentos($parteNome)));
            $parteNome = strtoupper(str_replace(' ','%',$parteNome));
            $query .= " AND nompes_upper LIKE '%{$parteNome}%' ";
        }
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

    public static function curso($codpes, $codundclgi)
    {
        $cols1 = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $cols2 = file_get_contents('replicado_queries/tables/vinculopessoausp.sql', true);
        $cols3 = file_get_contents('replicado_queries/tables/cursogr.sql', true);
        $cols4 = file_get_contents('replicado_queries/tables/habilitacaogr.sql', true);
        $query = " SELECT {$cols1},{$cols2},{$cols3},{$cols4} FROM LOCALIZAPESSOA "; 
        $query .= " INNER JOIN VINCULOPESSOAUSP ON (LOCALIZAPESSOA.codpes = VINCULOPESSOAUSP.codpes) "; 
        $query .= " INNER JOIN CURSOGR ON (VINCULOPESSOAUSP.codcurgrd = CURSOGR.codcur) "; 
        $query .= " INNER JOIN HABILITACAOGR ON (HABILITACAOGR.codhab = VINCULOPESSOAUSP.codhab) "; 
        $query .= " WHERE (LOCALIZAPESSOA.codpes = $codpes) ";
        $query .= " AND (LOCALIZAPESSOA.tipvin = 'ALUNOGR' AND LOCALIZAPESSOA.codundclg = '{$codundclgi}') ";
        $query .= " AND (VINCULOPESSOAUSP.codcurgrd = HABILITACAOGR.codcur AND VINCULOPESSOAUSP.codhab = HABILITACAOGR.codhab) ";
        $result = DB::fetch($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        return $result;
    }
}
