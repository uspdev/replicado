<?php

namespace Uspdev\Replicado;

class Pessoa 
{
    private $uteis;

    public static function dump($codpes)
    {
        $cols = file_get_contents('replicado_queries/tables/pessoa.sql', true);
        $query = " SELECT {$cols} FROM PESSOA WHERE codpes = {$codpes}"; 
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
        $cols = file_get_contents('replicado_queries/tables/catr_cracha.sql', true);
        $query = " SELECT {$cols} FROM CATR_CRACHA WHERE codpescra = '{$codpes}'"; 
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
        $cols = file_get_contents('replicado_queries/tables/emailpessoa.sql', true);
        $query = " SELECT {$cols} FROM EMAILPESSOA WHERE codpes = {$codpes}";
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
        $cols = file_get_contents('replicado_queries/tables/emailpessoa.sql', true);
        $query = " SELECT {$cols} FROM EMAILPESSOA WHERE codpes = {$codpes}";
        $result = DB::fetchAll($query);
        foreach($result as $row)
        {
            if (trim($row['stamtr'])=='S')
                return $row['codema'];
        }
    }

    public static function emailusp($codpes)
    {
        $cols = file_get_contents('replicado_queries/tables/emailpessoa.sql', true);
        $query = " SELECT {$cols} FROM EMAILPESSOA WHERE codpes = {$codpes}";
        $result = DB::fetchAll($query);
        foreach($result as $row)
        {
            if (trim($row['stausp'])=='S')
                return $row['codema'];
        }
    }

    public static function telefones($codpes)
    {
        $cols1 = file_get_contents('replicado_queries/tables/telefpessoa.sql', true);
        $cols2 = file_get_contents('replicado_queries/tables/localidade.sql', true);

        $query = " SELECT {$cols1}, {$cols2} FROM TELEFPESSOA ";
        $query .= " INNER JOIN LOCALIDADE ON TELEFPESSOA.codlocddd = LOCALIDADE.codloc ";
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

        $cols = file_get_contents('replicado_queries/tables/pessoa.sql', true);
        $query = " SELECT {$cols} "; 
        $query .= " FROM PESSOA WHERE UPPER(PESSOA.nompes) LIKE '%{$nome}%' "; 
        $query .= " ORDER BY PESSOA.nompes ASC "; 
        $result = DB::fetchAll($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        return $result;
    }

    public static function nomeFonetico($nome)
    {
        $cols = file_get_contents('replicado_queries/tables/pessoa.sql', true);
        $query  = "SELECT {$cols} "; 
        $query .= "FROM PESSOA WHERE PESSOA.nompesfon LIKE '%" . Uteis::fonetico($nome) .  "%' "; 
        $query .= "ORDER BY PESSOA.nompes ASC "; 
        $result = DB::fetchAll($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);

        return $result;
    }

    public static function localiza($codpes)
    {
        $cols = file_get_contents('replicado_queries/tables/localizapessoa.sql', true);
        $query = " SELECT {$cols} FROM LOCALIZAPESSOA WHERE codpes = {$codpes}"; 
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
}
