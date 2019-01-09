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

    public static function vinculos($codpes)
    {
        $query = " SELECT * FROM LOCALIZAPESSOA WHERE codpes = convert(int,:codpes)";
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
            if (!empty($row['sitatl']))
                $vinculo = $vinculo . " - " . $row['sitatl'];

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
dump($query);
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
        $query .= " WHERE (LOCALIZAPESSOA.tipvinext LIKE 'Estagiario' ";
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

}
