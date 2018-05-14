<?php

namespace Uspdev\Replicado;

class Graduacao 
{
    public static function verifica($codpes, $codundclgi)
    {
        $cols = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $query = " SELECT {$cols} FROM LOCALIZAPESSOA WHERE codpes = '{$codpes}'"; 
        $result = DB::fetchAll($query);

        foreach($result as $row)
        {
            if(trim($row['tipvin']) == 'ALUNOGR' && trim($row['sitatl']) == 'A'  && trim($row['codundclg']) == $codundclgi) 
                $return = true;
        }
        return false;
    }

    # Exemplo:
    # $strFiltro = "AND PESSOA.nompes LIKE '%Alessandro%'"
    public function ativos($codundclgi, $strFiltro = '')
    {
        $cols1 = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $cols2 = file_get_contents('replicado_queries/tables/pessoa.sql', true);
        $query = " SELECT {$cols1},{$cols2} FROM LOCALIZAPESSOA "; 
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) "; 
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ALUNOGR' AND LOCALIZAPESSOA.codundclg = '{$codundclgi}' ";
        $query .= " {$strFiltro} "; 
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

    public function curso($codpes, $codundclgi)
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
        $q = $this->conn->query($query);
        $result = $q->fetch();
        $result = $this->uteis->utf8_converter($result);
        $result = $this->uteis->trim_recursivo($result);

        return $result;
    }
}
