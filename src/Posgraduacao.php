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
            if (trim($row['tipvin']) == 'ALUNOPOS' && trim($row['sitatl']) == 'A' && trim($row['codundclg']) == $codundclgi) {
                return true;
            }

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
        $query = "SELECT C.codcur, NC.nomcur";
        $query .= " FROM CURSO AS C";
        $query .= " INNER JOIN NOMECURSO AS NC ON C.codcur = NC.codcur";
        $query .= " WHERE (C.codclg = :codundclgi) AND (C.tipcur = 'POS') AND (C.dtainiccp IS NOT NULL) AND (NC.dtafimcur IS NULL)";
        $param = ['codundclgi' => $codundclgi];
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

    /**
     * Retorna lista dos orientadores credenciados na área de concentração (codare) do programa de pós graduação correspondente.
     * 
     * Foi desvinculado de VINCULOPESSOAUSP pois recém credenciados pode ainda não ter vinculo ???
     * Issue #137
     *
     * @param  int $codare Código da área de concentração pertencente a um programa de pós.
     *
     * @return array
     */
    public static function orientadores(int $codare)
    {
        $query = "SELECT r.codpes, MAX(r.dtavalini) AS dtavalini, MAX(p.sexpes) AS sexpes,";
        $query .= " MAX(r.dtavalfim) AS dtavalfim, MIN(r.nivare) AS nivare, MIN(p.nompes) AS nompes";
        $query .= " FROM R25CRECREDOC as r, PESSOA as p";
        $query .= " WHERE r.codpes = p.codpes";
        $query .= " AND r.codare = :codare";
        $query .= " AND r.dtavalfim > CURRENT_TIMESTAMP";
        $query .= " GROUP BY r.codpes";
        $query .= " ORDER BY nompes ASC";

        $param = ['codare' => $codare];

        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        return $result;
    }

    public static function catalogoDisciplinas($codare)
    {
        $query = "SELECT DISTINCT r.sgldis, d.nomdis, r.numseqdis, r.dtaatvdis";
        $query .= " FROM R27DISMINCRE AS r, DISCIPLINA AS d";
        $query .= " WHERE d.sgldis = r.sgldis";
        $query .= " AND d.numseqdis = r.numseqdis";
        $query .= " AND r.codare = :codare";
        $query .= " AND (r.dtadtvdis IS NULL OR r.dtadtvdis > getdate())"; // não está desativado
        $query .= " AND d.dtaatvdis IS NOT NULL"; // está ativado
        $query .= " AND dateadd(yy,5,d.dtaatvdis)>=getdate()"; // disciplina mais nova que 5 anos
        $query .= " ORDER BY d.nomdis ASC";

        $param = ['codare' => $codare];

        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    public static function disciplina($sgldis)
    {
        $query = "SELECT TOP 1 * FROM DISCIPLINA";
        $query .= " WHERE sgldis = :sgldis";
        $query .= " ORDER BY numseqdis DESC";

        $param = ['sgldis' => $sgldis];

        $result = DB::fetchAll($query, $param);
        if ($result) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result[0];
        } else {
            return [];
        }
    }

    /**
     * Retorna a lista de disciplinas em oferecimento de uma determinada área de concentração.
     * 
     * Se $data não for informado pega a data corrente. Se for informado pegará as disciplinas oferecidas
     * no semestre que contém a data.
     *
     * @param int $codare Código da áreada PG.
     * @param string $data (opcional) Data na qual vai buscar os limites do semestre.
     *
     * @return void
     */
    public static function disciplinasOferecimento(int $codare, string $data = null)
    {
        $inifim = Uteis::semestre($data);

        $query = "SELECT  e.sgldis, MAX(e.numseqdis) AS numseqdis, o.numofe, d.nomdis";
        $query .= " FROM oferecimento AS o, R27DISMINCRE AS r, espacoturma AS e, disciplina AS d";
        $query .= " WHERE e.sgldis = d.sgldis";
        $query .= " AND e.sgldis = r.sgldis";
        $query .= " AND o.sgldis = r.sgldis";
        $query .= " AND o.numseqdis = d.numseqdis";
        $query .= " AND o.dtainiofe > :dtainiofe";
        $query .= " AND o.dtafimofe < :dtafimofe";
        $query .= " AND r.codare = :codare";
        $query .= " GROUP BY e.sgldis, d.nomdis, o.numofe";
        $query .= " ORDER BY d.nomdis ASC";

        $param = [
            'codare' => $codare,
            'dtainiofe' => $inifim[0],
            'dtafimofe' => $inifim[1]
        ];

        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }
}
