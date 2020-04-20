<?php

namespace Uspdev\Replicado;

class Genero
{
    /**
     * Método para retornar o total de alunos de graduação do gênero especificado
     * @param Integer $codundclg
     * @param Char $sexpes
     * @return void
     */
    public static function contaAlunoGR($codundclg, $sexpes){
        $query = " SELECT COUNT (DISTINCT LOCALIZAPESSOA.codpes) FROM LOCALIZAPESSOA ";
        $query .= " JOIN PESSOA ON PESSOA.codpes = LOCALIZAPESSOA.codpes ";
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ALUNOGR' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = convert(int,:codundclg) ";
        $query .= " AND PESSOA.sexpes = $sexpes ";
        $param = [
            'codundclg' => $codundclg,
            'sexpes' => $sexpes,
        ];
        $result = DB::fetch($query, $param);
        if (!empty($result)) {
            return $result;
        }
        return false;
    }

    /**
     * Método para retornar o total de alunos do curso de graduação do gênero especificado
     * @param Integer $codundclg
     * @param Char $sexpes
     * @return void
     */
    public static function contaAlunoGRCurso($codundclg, $sexpes, $codcur){
        $query = " SELECT COUNT (DISTINCT LOCALIZAPESSOA.codpes) FROM LOCALIZAPESSOA ";
        $query .= " JOIN PESSOA ON PESSOA.codpes = LOCALIZAPESSOA.codpes ";
        $query .= " JOIN SITALUNOATIVOGR ON SITALUNOATIVOGR.codpes = LOCALIZAPESSOA.codpes ";
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ALUNOGR' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = convert(int,:codundclg) ";
        $query .= " AND PESSOA.sexpes = $sexpes AND SITALUNOATIVOGR.codcur = convert(int,:codcur) ";
        $param = [
            'codundclg' => $codundclg,
            'sexpes' => $sexpes,
            'codcur' => $codcur,
        ];
        $result = DB::fetch($query, $param);
        if (!empty($result)) {
            return $result;
        }
        return false;
    }

    /**
     * Método para retornar o total de docentes ativos do gênero especificado
     * @param Integer $codundclg
     * @param Char $sexpes
     * @return void
     */
    public static function contaDocente($codundclg, $sexpes){
        $query = " SELECT COUNT (DISTINCT LOCALIZAPESSOA.codpes) FROM LOCALIZAPESSOA ";
        $query .= " JOIN PESSOA ON PESSOA.codpes = LOCALIZAPESSOA.codpes ";
        $query .= " WHERE LOCALIZAPESSOA.tipvinext = 'Docente' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = convert(int,:codundclg) ";
        $query .= " AND PESSOA.sexpes = $sexpes AND LOCALIZAPESSOA.sitatl = 'A' ";
        $param = [
            'codundclg' => $codundclg,
            'sexpes' => $sexpes,
        ];
        $result = DB::fetch($query, $param);
        if (!empty($result)) {
            return $result;
        }
        return false;
    }

    /**
     * Método para retornar o total de estágiarios ativos na unidade do gênero especificado
     * @param Integer $codundclg
     * @param Char $sexpes
     * @return void
     */
    public static function contaEstagiario($codundclg, $sexpes){
        $query = " SELECT COUNT (DISTINCT LOCALIZAPESSOA.codpes) FROM LOCALIZAPESSOA ";
        $query .= " JOIN PESSOA ON PESSOA.codpes = LOCALIZAPESSOA.codpes ";
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ESTAGIARIORH' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = convert(int,:codundclg) ";
        $query .= " AND PESSOA.sexpes = $sexpes ";
        $param = [
            'codundclg' => $codundclg,
            'sexpes' => $sexpes,
        ];
        $result = DB::fetch($query, $param);
        if (!empty($result)) {
            return $result;
        }
        return false;
    }
}
