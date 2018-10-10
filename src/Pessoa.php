<?php

namespace Uspdev\Replicado;

class Pessoa
{
    private $uteis;

    public static function dump($codpes)
    {
        $query = " SELECT * FROM PESSOA WHERE codpes = :codpes";
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
        $query = " SELECT * FROM EMAILPESSOA WHERE codpes = :codpes";
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
        $query = " SELECT * FROM EMAILPESSOA WHERE codpes = :codpes";
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
        $query = " SELECT * FROM EMAILPESSOA WHERE codpes = :codpes";
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
        $query .= " WHERE TELEFPESSOA.codpes = :codpes";
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

    public static function localiza($codpes)
    {
        $query = " SELECT * FROM LOCALIZAPESSOA WHERE codpes = :codpes";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        $localizas = array();
        foreach ($result as $row)
        {
            $localiza = "";
            if (!empty($row['tipvinext']))
                $localiza = $localiza  .  $row['tipvinext'];

            if (!empty($row['nomfnc']))
                $localiza = $localiza . " - " . $row['nomfnc'];

            if (!empty($row['nomset']))
                $localiza = $localiza . " - " . $row['nomset'];

            if (!empty($row['sglclgund']))
                $localiza = $localiza . " - " . $row['sglclgund'];

            in_array($localiza,$localizas) ?:  array_push($localizas,$localiza);

        }
        return $localizas;
    }

    /**
     * Método para retornar vículos ativos de uma pessoa
     *
     * @param Integer $codundclgi
     * @return void
     */

    public static function vinculosAtivos($codpes)
    {
        $query = " SELECT * FROM LOCALIZAPESSOA WHERE codpes = :codpes AND LOCALIZAPESSOA.sitatl = 'A'";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        $vinculos = array();
        foreach ($result as $row)
        {
            if (!empty($row['tipvinext']))
                in_array($row['tipvinext'],$vinculos) ?:  array_push($vinculos,$row['tipvinext'];

        }
        return $vinculos;
    }

    /**
     * Método para retornar docentes ativos na unidade
     *
     * @param Integer $codundclgi
     * @return void
     */
    public static function docentesAtivos($codundclgi)
    {
        $query  = " SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA ";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) ";
        $query .= " WHERE (LOCALIZAPESSOA.tipvinext LIKE 'Docente%
' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = :codundclgi AND LOCALIZAPESSOA.sitatl = 'A') ";
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
    public static function servidoresAtivos($codundclgi)
    {
        $query  = " SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA ";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) ";
        $query .= " WHERE (LOCALIZAPESSOA.tipvinext LIKE 'Servidor' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = :codundclgi AND LOCALIZAPESSOA.sitatl = 'A') ";
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
    public static function designados($codundclgi)
    {
        $query  = " SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA ";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes) ";
        $query .= " WHERE (LOCALIZAPESSOA.tipvinext LIKE 'Servidor Designado' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = :codundclgi AND LOCALIZAPESSOA.sitatl = 'A') ";
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
