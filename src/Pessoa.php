<?php

namespace Uspdev\Replicado;

class Pessoa
{
    private $uteis;

    public static function dump($codpes)
    {
        $query = " SELECT * FROM PESSOA WHERE codpes = {$codpes}";
        $result = DB::fetch($query);
        if(!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }

    public static function cracha($codpes)
    {
        $query = " SELECT * FROM CATR_CRACHA WHERE codpescra = '{$codpes}'";
        $result = DB::fetch($query);
        if(!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }

    public static function emails($codpes)
    {
        $query = " SELECT * FROM EMAILPESSOA WHERE codpes = {$codpes}";
        $result = DB::fetchAll($query);
        $emails= array();
        foreach($result as $row)
        {
            $email = trim($row['codema']);
            in_array($email,$emails) ?: array_push($emails,$email);
        }
        return $emails;
    }

    public static function email($codpes)
    {
        $query = " SELECT * FROM EMAILPESSOA WHERE codpes = {$codpes}";
        $result = DB::fetchAll($query);
        foreach($result as $row)
        {
            if (trim($row['stamtr'])=='S')
                return $row['codema'];
        }
    }

    public static function emailusp($codpes)
    {
        $query = " SELECT * FROM EMAILPESSOA WHERE codpes = {$codpes}";
        $result = DB::fetchAll($query);
        foreach($result as $row)
        {
            if (trim($row['stausp'])=='S')
                return $row['codema'];
        }
    }

    public static function telefones($codpes)
    {
        $query = " SELECT * FROM TELEFPESSOA ";
        $query .= " WHERE TELEFPESSOA.codpes = {$codpes}";
        $result = DB::fetchAll($query);

        $telefones= array();
        foreach($result as $row)
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
        $nome= strtoupper(str_replace(' ','%',$nome));

        $query = " SELECT *"; 
        $query .= " FROM PESSOA WHERE UPPER(PESSOA.nompes) LIKE '%{$nome}%' "; 
        $query .= " ORDER BY PESSOA.nompes ASC "; 
        $result = DB::fetchAll($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        return $result;
    }

    public static function nomeFonetico($nome)
    {
        $query  = "SELECT *"; 
        $query .= " FROM PESSOA WHERE PESSOA.nompesfon LIKE '%" . Uteis::fonetico($nome) .  "%' "; 
        $query .= "ORDER BY PESSOA.nompes ASC "; 
        $result = DB::fetchAll($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        return $result;
    }

    public static function localiza($codpes)
    {
        $query = " SELECT * FROM LOCALIZAPESSOA WHERE codpes = {$codpes}";
        $result = DB::fetchAll($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        $localizas = array();
        foreach($result as $row)
        {
            $localiza = "";
            if(!empty($row['tipvinext']))
                $localiza = $localiza  .  $row['tipvinext'];

            if(!empty($row['nomfnc']))
                $localiza = $localiza . " - " . $row['nomfnc'];

            if(!empty($row['nomset']))
                $localiza = $localiza . " - " . $row['nomset'];

            if(!empty($row['sglclgund']))
                $localiza = $localiza . " - " . $row['sglclgund'];

            in_array($localiza,$localizas) ?:  array_push($localizas,$localiza);

        }
        return $localizas;
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
        $query .= " WHERE (LOCALIZAPESSOA.tipvinext LIKE 'Docente%' ";
        $query .= " AND LOCALIZAPESSOA.codundclg = {$codundclgi} AND LOCALIZAPESSOA.sitatl = 'A') ";
        $query .= " ORDER BY LOCALIZAPESSOA.nompes ";
        $result = DB::fetchAll($query);
        if(!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }
}
