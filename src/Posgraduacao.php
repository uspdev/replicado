<?php

namespace Uspdev\Replicado;

class Posgraduacao
{
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
        $query .= " FROM ECA.dbo.CURSO AS C"; 
        $query .= " INNER JOIN NOMECURSO AS NC ON C.codcur = NC.codcur";
        if (!is_null($codcur)) {
            $param  = [
                'codundclgi'    => $codundclgi,
                'codcur'        => $codcur,
            ];
            $query .= " WHERE (C.codclg = :codundclgi) AND (C.tipcur = 'POS') AND (C.dtainiccp IS NOT NULL) AND (C.codcur = :codcur)";
        } else {
            $param  = [
                'codundclgi' => $codundclgi,
            ];
            $query .= " WHERE (C.codclg = :codundclgi) AND (C.tipcur = 'POS') AND (C.dtainiccp IS NOT NULL)";
        }
        $query .= " ORDER BY NC.nomcur ASC ";    
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }
}
