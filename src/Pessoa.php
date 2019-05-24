<?php

namespace Uspdev\Replicado;

class Pessoa
{
    private $uteis;

    public static function dump($codpes)
    {
        $query = " SELECT * FROM PESSOA WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetch($query, $param);
        if (!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }

    public static function cracha($codpes)
    {
        $query = " SELECT * FROM CATR_CRACHA WHERE codpescra = :codpes";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetch($query, $param);
        if (!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }

    public static function emails($codpes)
    {
        $query = " SELECT * FROM EMAILPESSOA WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);
        $emails= array();
        foreach ($result as $row)
        {
            $email = trim($row['codema']);
            in_array($email,$emails) ?: array_push($emails,$email);
        }
        return $emails;
    }

    public static function email($codpes)
    {
        $query = " SELECT * FROM EMAILPESSOA WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);
        foreach ($result as $row)
        {
            if (trim($row['stamtr'])=='S')
                return $row['codema'];
        }
    }

    public static function emailusp($codpes)
    {
        $query = " SELECT * FROM EMAILPESSOA WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);
        foreach($result as $row)
        {
            if (trim($row['stausp'])=='S') {
                return $row['codema'];
            }
            # adicionado o codigo abaixo, porque têm
            # e-mail usp que o campo 'stausp' não está marcado
            if (!is_null(trim($row['codema']))) {
                $emailusp = strpos($row['codema'],'usp.br');
                if ($emailusp != false)
                    return $row['codema'];
            }
        }
        return "e-mail usp não encontrado";
    }

    public static function telefones($codpes)
    {
        $query = " SELECT * FROM TELEFPESSOA ";
        $query .= " WHERE TELEFPESSOA.codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);

        $telefones = array();
        foreach ($result as $row)
        {
            $telefone = '(' . trim($row['codddd']) . ') ' . trim($row['numtel']);
            in_array($telefone,$telefones) ?: array_push($telefones,$telefone);
        }
        return $telefones;
    }

    public static function nome($nome)
    {
        $nome = utf8_decode(Uteis::removeAcentos($nome));
        $nome = trim($nome);
        $nome = strtoupper(str_replace(' ','%',$nome));

        $query = " SELECT *"; 
        $query .= " FROM PESSOA WHERE UPPER(PESSOA.nompes) LIKE :nome"; 
        $query .= " ORDER BY PESSOA.nompes ASC "; 
        $param = [
            'nome' => '%' . $nome . '%',
        ];
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        return $result;
    }

    public static function nomeFonetico($nome)
    {
        $query  = "SELECT *"; 
        $query .= " FROM PESSOA WHERE PESSOA.nompesfon LIKE :nome";
        $query .= " ORDER BY PESSOA.nompes ASC";
        $param = [
            'nome' => '%' . Uteis::fonetico($nome) . '%',
        ];
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        return $result;
    }

    /**
     * Método para retornar vínculos de uma pessoa
     *
     * @param Integer $codpes
     * @return void
     */

    public static function vinculos(int $codpes, int $codundclgi = 0)
    {
        $query = " SELECT * FROM LOCALIZAPESSOA WHERE codpes = convert(int,:codpes)";
        if($codundclgi != 0 ) {
            $query .= " AND codundclg = convert(int,:codundclgi)";
        }
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        $vinculos = array();
        foreach ($result as $row)
        {

            $vinculo = "";
            if (!empty($row['tipvinext']))
                $vinculo = $vinculo  .  $row['tipvinext'];
            if (!empty($row['nomfnc']))
                $vinculo = $vinculo . " - " . $row['nomfnc'];
            if (!empty($row['nomset']))
                $vinculo = $vinculo . " - " . $row['nomset'];
            if (!empty($row['sglclgund']))
                $vinculo = $vinculo . " - " . $row['sglclgund'];

            in_array($vinculo,$vinculos) ?:  array_push($vinculos,$vinculo);

        }
        return $vinculos;
    }

    /**
     * Método que retornar sigals dos vínculos de uma pessoa em uma dada unidade
     *
     * @param Integer $codpes
     * @return array
     */

    public static function vinculosSiglas(int $codpes, int $codundclgi = 0)
    {
        $query = " SELECT * FROM LOCALIZAPESSOA WHERE codpes = convert(int,:codpes) AND sitatl = 'A'";
        if($codundclgi != 0 ) {
            $query .= " AND codundclg = convert(int,:codundclgi)";
        }
        $param = [
            'codpes' => $codpes,
            'codundclgi' => $codundclgi,
        ];
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        $vinculos = array();
        foreach ($result as $row)
        {
            if (!empty($row['tipvin']))
                $vinculo = trim($row['tipvin']);
                in_array($vinculo,$vinculos) ?:  array_push($vinculos,$vinculo);
        }
        return $vinculos;
    }

    /**
     * Método para retornar docentes ativos na unidade
     *
     * @param Integer $codundclgi
     * @return void
     */
    public static function docentes($codundclgi)
    {
        $query  = " SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA ";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) ";
        $query .= " WHERE (LOCALIZAPESSOA.tipvinext LIKE 'Docente' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = convert(int,:codundclgi) AND LOCALIZAPESSOA.sitatl = 'A') ";
        $query .= " ORDER BY LOCALIZAPESSOA.nompes ";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        $result = DB::fetchAll($query, $param);
        if(!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }
    
   /**
     * Método para retornar servidores ativos na unidade
     *
     * @param Integer $codundclgi
     * @return void
     */
    public static function servidores($codundclgi)
    {
        $query  = " SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA ";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) ";
        $query .= " WHERE (LOCALIZAPESSOA.tipvinext LIKE 'Servidor' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = convert(int,:codundclgi) AND LOCALIZAPESSOA.sitatl = 'A') ";
        $query .= " ORDER BY LOCALIZAPESSOA.nompes ";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        $result = DB::fetchAll($query, $param);
        if(!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }

   /**
     * Método para retornar servidores designados ativos na unidade
     *
     * @param Integer $codundclgi
     * @return void
     */
    public static function designados($codundclgi)
    {
        $query  = " SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA ";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) ";
        $query .= " WHERE (LOCALIZAPESSOA.tipvinext LIKE 'Servidor Designado' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = convert(int,:codundclgi) AND LOCALIZAPESSOA.sitatl = 'A') ";
        $query .= " ORDER BY LOCALIZAPESSOA.nompes ";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        $result = DB::fetchAll($query, $param);
        if(!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }
    
    /**
     * Método para retornar estagiários ativos na unidade
     *
     * @param Integer $codundclgi
     * @return void
     */
    public static function estagiarios($codundclgi)
    {
        $query  = " SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA ";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) ";
        $query .= " WHERE ( LOCALIZAPESSOA.tipvin LIKE 'ESTAGIARIORH'";
        $query .= " AND LOCALIZAPESSOA.codundclg = convert(int,:codundclgi) AND LOCALIZAPESSOA.sitatl = 'A') ";
        $query .= " ORDER BY LOCALIZAPESSOA.nompes ";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        $result = DB::fetchAll($query, $param);
        if(!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }

    /**
     * Método para retornar o total dos vinculos
     *
     * @param Integer $codundclg
     * @param String $vinculo
     * @param Char $situacao
     * @return void
     */
    public static function totalVinculo($vinculo, $codundclg)
    {
        $query = "SELECT COUNT(codpes) as totalvinculo FROM LOCALIZAPESSOA ";
        $query .= "WHERE tipvinext = :vinculo AND sitatl = 'A' AND ";
        $query .= "codundclg = convert(int,:codundclg)";
        $param = [
            'vinculo' => utf8_decode($vinculo),
            'codundclg' => $codundclg,
        ];
        $result = DB::fetchAll($query, $param);
        if(!empty($result)) {
            return $result;
        }
        return false;
    }

    /**
     * Método para retornar o total do programa de Pós
     *
     * @param Integer $codundclg
     * @param String $nivpgm
     * @return void
     */
    public static function totalPosNivelPrograma($nivpgm, $codundclg)
    {
        $query = "SELECT COUNT(lp.codpes) AS totalnivpgm FROM LOCALIZAPESSOA AS lp ";
        $query .= "INNER JOIN VINCULOPESSOAUSP AS vpu ";
        $query .= "ON(lp.codpes = vpu.codpes AND lp.tipvin = vpu.tipvin) ";
        $query .= "WHERE lp.tipvin='ALUNOPOS' AND lp.codundclg= convert(int,:codundclg) ";
        $query .= "AND lp.sitatl='A' AND vpu.nivpgm=:nivpgm";

        $param = [
            'nivpgm' => $nivpgm,
            'codundclg' => $codundclg,
        ];
        $result = DB::fetchAll($query, $param);
        if(!empty($result)) {
            return $result;
        }
        return false;
    }

    /**
     * Método para retornar todos os vínculos por extenso
     *
     * @return void
     */
    public static function todosVinculosExtenso()
    {
        $query = "SELECT DISTINCT(tipvinext) FROM LOCALIZAPESSOA";
        $result = DB::fetchAll($query);
        if(!empty($result)) {
            return Uteis::utf8_converter($result);
        }
        return false;
    }

}
