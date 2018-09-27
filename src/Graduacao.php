<?php

namespace Uspdev\Replicado;

class Graduacao
{
    public static function verifica($codpes, $codundclgi)
    {
        $query = " SELECT * FROM LOCALIZAPESSOA WHERE codpes = :codpes"; 
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);

        $return = false;
        foreach ($result as $row)
        {
            if (trim($row['tipvin']) == 'ALUNOGR' && 
               trim($row['sitatl']) == 'A'  && 
               trim($row['codundclg']) == $codundclgi) 
               $return = true;
        }
        return $return;
    }

    public static function ativos($codundclgi, $parteNome = null)
    {
        $param = [
            'codundclgi' => $codundclgi,
        ];
        $query = " SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes)";
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ALUNOGR' AND LOCALIZAPESSOA.codundclg = :codundclgi";
        if (!is_null($parteNome)) {
            $parteNome = trim(utf8_decode(Uteis::removeAcentos($parteNome)));
            $parteNome = strtoupper(str_replace(' ','%',$parteNome));
            $query .= " AND PESSOA.nompesfon LIKE :parteNome";
            $param['parteNome'] = '%' . Uteis::fonetico($parteNome) . '%';
        }
        $query .= " ORDER BY PESSOA.nompes ASC";
        $result = DB::fetchAll($query, $param);
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
        $query = " SELECT LOCALIZAPESSOA.*, VINCULOPESSOAUSP.*, CURSOGR.*, HABILITACAOGR.* FROM LOCALIZAPESSOA";
        $query .= " INNER JOIN VINCULOPESSOAUSP ON (LOCALIZAPESSOA.codpes = VINCULOPESSOAUSP.codpes)";
        $query .= " INNER JOIN CURSOGR ON (VINCULOPESSOAUSP.codcurgrd = CURSOGR.codcur)";
        $query .= " INNER JOIN HABILITACAOGR ON (HABILITACAOGR.codhab = VINCULOPESSOAUSP.codhab)";
        $query .= " WHERE (LOCALIZAPESSOA.codpes = :codpes)";
        $query .= " AND (LOCALIZAPESSOA.tipvin = 'ALUNOGR' AND LOCALIZAPESSOA.codundclg = :codundclgi)";
        $query .= " AND (VINCULOPESSOAUSP.codcurgrd = HABILITACAOGR.codcur AND VINCULOPESSOAUSP.codhab = HABILITACAOGR.codhab)";
        $param = [
            'codpes' => $codpes,
            'codundclgi' => $codundclgi,
        ];
        $result = DB::fetch($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    public static function programa($codpes)
    {
        $query = " SELECT TOP 1 * FROM HISTPROGGR ";
        $query .= " WHERE (HISTPROGGR.codpes = :codpes) ";
        $query .= " AND (HISTPROGGR.stapgm = 'H' OR HISTPROGGR.stapgm = 'R') ";
        $query .= " ORDER BY HISTPROGGR.dtaoco DESC ";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetch($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    public static function nomeCurso($codcur)
    {
        $query = " SELECT TOP 1 * FROM CURSOGR ";
        $query .= " WHERE (CURSOGR.codcur = :codcur) ";
        $param = [
            'codcur' => $codcur,
        ];
        $result = DB::fetch($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result['nomcur'];
    }

    public static function nomeHabilitacao($codhab, $codcur)
    {
        $query = " SELECT TOP 1 * FROM HABILITACAOGR ";
        $query .= " WHERE (HABILITACAOGR.codhab = :codhab AND HABILITACAOGR.codcur = :codcur) ";
        $param = [
            'codhab' => $codhab,
            'codcur' => $codcur,
        ];
        $result = DB::fetch($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result['nomhab'];
    }

    public static function obterCursosHabilitacoes($codundclgi)
    {
        $query = " SELECT CURSOGR.*, HABILITACAOGR.* FROM CURSOGR, HABILITACAOGR";
        $query .= " WHERE (CURSOGR.codclg = :codundclgi) AND (CURSOGR.codcur = HABILITACAOGR.codcur)";
        $query .= " AND ( (CURSOGR.dtaatvcur IS NOT NULL) AND (CURSOGR.dtadtvcur IS NULL) )";
        $query .= " AND ( (HABILITACAOGR.dtaatvhab IS NOT NULL) AND (HABILITACAOGR.dtadtvhab IS NULL) )";
        $query .= " ORDER BY CURSOGR.nomcur, HABILITACAOGR.nomhab ASC";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    /**
     * Método para obter as disciplinas de graduação oferecidas na unidade
     *
     * @param Array $arrCoddis
     * @return void
     */
    public static function obterDisciplinas($arrCoddis)
    {
        $query = " SELECT D1.* FROM DISCIPLINAGR AS D1";
        $query .= " WHERE (D1.verdis = (
            SELECT MAX(D2.verdis) FROM DISCIPLINAGR AS D2 WHERE (D2.coddis = D1.coddis) 
        )) AND ( ";
        foreach ($arrCoddis as $sgldis) {
            $query .= " (D1.coddis LIKE '$sgldis%') OR ";
        }
        $query = substr($query, 0, -3);
        $query .= " ) ";
        $query .= " ORDER BY D1.coddis ASC"; 
        $result = DB::fetchAll($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    /**
     * Método para trazer o nome da disciplina de graduação
     *
     * @param String $coddis
     * @return void
     */
    public static function nomeDisciplina($coddis)
    {
        $query = " SELECT D1.* FROM DISCIPLINAGR AS D1";
        $query .= " WHERE (D1.verdis = (
            SELECT MAX(D2.verdis) FROM DISCIPLINAGR AS D2 WHERE (D2.coddis = D1.coddis)
        )) AND (D1.coddis = :coddis)";
        $param = [
            'coddis' => $coddis,
        ];
        $result = DB::fetch($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result['nomdis'];
    }

    /**
     * Método para trazer as disciplinas, status e créditos concluídos
     *
     * @param Int $codpes
     * @return void
     */
    public static function disciplinasConcluidas($codpes, $codundclgi)
    {
        $programa = self::programa($codpes);
        $programa = $programa['codpgm'];
        $ingresso = self::curso($codpes, $codundclgi);
        $ingresso = substr($ingresso['dtainivin'], 0, 10);
        $query  = "SELECT DISTINCT H.coddis, H.rstfim, D.creaul FROM HISTESCOLARGR AS H, DISCIPLINAGR AS D ";
        $query .= "WHERE H.coddis = D.coddis AND H.verdis = D.verdis AND H.codpes = :codpes AND H.codpgm = :programa ";
        $query .= "AND	(H.codtur = '0' OR CONVERT(INT, CONVERT(CHAR(4), H.codtur)) >= YEAR(:ingresso)) ";
        $query .= "AND (H.rstfim = 'A' OR H.rstfim = 'D' OR (H.rstfim = NULL AND H.stamtr = 'M' AND H.codtur LIKE 'YEAR(:ingresso)1%')) ";
        $query .= "ORDER BY H.coddis";
        $param = [
            'codpes' => $codpes,
            'programa' => $programa,
            'ingresso' => $ingresso,
            'ingresso' => $ingresso,
        ];
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

}
