<?php

namespace Uspdev\Replicado;

class Posgraduacao
{
    // verifica se aluno (codpes) tem matrícula ativa na pós-graduação da unidade
    public static function verifica($codpes, $codundclgi)
    {
        $query = " SELECT * FROM LOCALIZAPESSOA WHERE codpes = :codpes";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);
        
        foreach ($result as $row) {
            if (trim($row['tipvin']) == 'ALUNOPOS' && trim($row['sitatl']) == 'A'  && trim($row['codundclg']) == $codundclgi)
                return true;
        }
        return false;
    }

    public static function ativos($codundclgi)
    {
        $query = "SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes)";
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ALUNOPOS' AND LOCALIZAPESSOA.codundclg = :codundclgi AND LOCALIZAPESSOA.sitatl = 'A'";
        $query .= " ORDER BY PESSOA.nompes ASC ";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    public static function programas($codundclgi, $codcur = null)
    {
        $query  = "SELECT C.codcur, NC.nomcur";
        $query .= " FROM CURSO AS C"; 
        $query .= " INNER JOIN NOMECURSO AS NC ON C.codcur = NC.codcur";
        $query .= " WHERE (C.codclg = :codundclgi) AND (C.tipcur = 'POS') AND (C.dtainiccp IS NOT NULL) AND (NC.dtafimcur IS NULL)";
        $param  = ['codundclgi' => $codundclgi];
        if (!is_null($codcur)) {
            $param['codcur'] = $codcur;
            $query .= " AND (C.codcur = :codcur)";
        } 
        $query .= " ORDER BY NC.nomcur ASC ";    
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    public static function orientadores($codare) {
        $query = "SELECT distinct r.codpes, r.nivare, r.dtavalini, r.dtavalfim, v.nomabvfnc, p.nompes, p.sexpes";
        $query .= " FROM R25CRECREDOC as r";
        $query .= " LEFT OUTER JOIN VINCULOPESSOAUSP as v on v.codpes = r.codpes";
        $query .= " LEFT OUTER JOIN PESSOA as p on p.codpes = r.codpes";
        $query .= " WHERE r.codare = :codare";
        $query .= " AND v.nomcaa = 'Docente'";
        $query .= " AND r.dtavalfim > current_timestamp";

        $param  = ['codare' => $codare];

        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;

    }

    public static function catalogoDisciplinas($codare) {
        $query = "SELECT DISTINCT d.sgldis, d.nomdis";
        $query .= " FROM R27DISMINCRE AS r, DISCIPLINA AS d";
        $query .= " WHERE d.sgldis = r.sgldis";
        $query .= " AND d.numseqdis = r.numseqdis";
        $query .= " AND r.codare = :codare";
        $query .= " AND (r.dtadtvdis IS null OR r.dtadtvdis > getdate())";
        $query .= " AND d.dtaatvdis IS NOT NULL"; // está ativado
        $query .= " AND d.dtadtvdis IS NULL"; // não está desativado
        $query .= " AND dateadd(yy,5,d.dtaatvdis)>=getdate()"; // disciplina mais nova que 5 anos
        $query .= " ORDER BY d.nomdis ASC";

        $param  = ['codare' => $codare];

        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }
}
