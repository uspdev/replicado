<?php

namespace Uspdev\Replicado;

class Pessoa
{
    private $uteis;

    public static function dump(int $codpes, array $fields = ['*'])
    {
        $columns = implode(",",$fields);
        $query = " SELECT {$columns} FROM PESSOA WHERE codpes = convert(int,:codpes)";
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
     * Método que retornar siglas dos vínculos de uma pessoa em uma dada unidade
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

    public static function setoresSiglas(int $codpes, int $codundclgi = 0)
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

        $setores = array();
        foreach ($result as $row)
        {
            if (!empty(trim($row['nomabvset']))) {
                $setor = trim($row['nomabvset']);
                in_array($setor,$setores) ?: array_push($setores,$setor);
            }
        }
        return $setores;
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
            'vinculo' => $vinculo,
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
    
    /**
     * Retorna o nome completo (nome social) a partir do codpes
     * @param type $codpes
     * @return boolean
     */
    public static function nomeCompleto($codpes){
        $pessoa = Pessoa::dump($codpes, ['nompesttd']);
        if(!empty($pessoa)) {
            return $pessoa;
        }
        return false;
    }

    /**
     * Método para retornar todos os tipos de vínculos possíveis
     * Somente ATIVOS: alunos regulares, tipvin IN ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD'),
     * funcionários, estagiários e docentes, tipvin IN ('SERVIDOR', 'ESTAGIARIORH') 
     * Incluido também os Docente Aposentado 
     *
     * @param Integer $codundclgi
     * @return void
     */
    public static function tiposVinculos($codundclgi)
    {
        $query = "SELECT DISTINCT tipvinext FROM LOCALIZAPESSOA 
                    WHERE sitatl IN ('A', 'P') AND codundclg = convert(int, :codundclgi) 
                    AND (tipvin IN ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'SERVIDOR', 'ESTAGIARIORH'))
                    AND (tipvinext NOT IN ('Servidor Designado', 'Servidor Aposentado'))
                    ORDER BY tipvinext";
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
     * Método para retornar todas as pessoas ativas por vínculo
     * Somente ATIVOS
     * Também Docente Aposentado 
     *
     * @param String $vinculo
     * @param Integer $codundclgi
     * @return void
     */
    public static function ativosVinculo($vinculo, $codundclgi) 
    {
        $query = "SELECT L.*, P.* FROM LOCALIZAPESSOA AS L 
                    INNER JOIN PESSOA AS P ON (L.codpes = P.codpes) 
                    WHERE (L.tipvinext = :vinculo AND L.codundclg = CONVERT(INT, :codundclgi) AND L.sitatl IN ('A', 'P')) 
                    ORDER BY L.nompes";
        # Neste método foi necessário verificar o SGBD por conta do CHARSET utilizado pelo replicado
        $sgbd = DB::getSgbd();
        $vinculo = ($sgbd == 'sybase') ? iconv('UTF-8', 'ISO-8859-1', $vinculo) : $vinculo;
        $param = [
            'codundclgi'    => $codundclgi,
            'vinculo'       => $vinculo,
        ];
        $result = DB::fetchAll($query, $param);
        if (!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    } 
}
