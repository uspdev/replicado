<?php

namespace Uspdev\Replicado;

class Pessoa
{

    /**
     * Método que recebe codpes e retorna todos campos da tabela Pessoa para o codpes em questão.
     * O campos $fields é opcional.
     *
     * @param Integer $codpes
     * @param array $fields
     * @return void
     */
    public static function dump(int $codpes, array $fields = ['*'])
    {
        $columns = implode(",", $fields);
        $query = "SELECT {$columns} FROM PESSOA
                    WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        return DB::fetch($query, $param);
    }

    /**
     * Método que recebe codpes para retornar todos os campos da tabela cracha para o codpes em questão
     *
     * @param Integer $codpes
     * @return array
     */
    public static function cracha($codpes)
    {
        $query = "SELECT * FROM CATR_CRACHA
                    WHERE codpescra = :codpes";
        $param = [
            'codpes' => $codpes,
        ];
        return DB::fetch($query, $param);
    }

    /**
     * Método que recebe número USP para retornar array com todos emails da pessoa
     *
     * @param Integer $codpes
     * @return array
     */
    public static function emails($codpes)
    {
        $query = "SELECT * FROM EMAILPESSOA
                    WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);
        $emails = [];
        foreach ($result as $row) {
            $email = trim($row['codema']);
            in_array($email, $emails) ?: array_push($emails, $email);
        }
        return $emails;
    }

    /**
     * Método que recebe o número USP para retornar email de correspondência da pessoa,
     * cujo campo 'stamtr' é igual a 'S'
     *
     * @param Integer $codpes
     * @return String
     */
    public static function email($codpes)
    {
        $query = "SELECT * FROM EMAILPESSOA
                    WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);
        foreach ($result as $row) {
            if (trim($row['stamtr']) == 'S') {
                return $row['codema'];
            }

        }
        return false;
    }

    /**
     * Método que recebe o número USP e retorna array com telefones da pessoa
     *
     * @param Integer $codpes
     * @return array
     */
    public static function telefones($codpes)
    {
        $query = "SELECT * FROM TELEFPESSOA
                    WHERE TELEFPESSOA.codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);

        $telefones = array();
        foreach ($result as $row) {
            $telefone = '(' . trim($row['codddd']) . ') ' . trim($row['numtel']);
            in_array($telefone, $telefones) ?: array_push($telefones, $telefone);
        }
        return $telefones;
    }

    /**
     * Método para buscar pessoas por nome ou parte do nome
     * A busca pode ser fonética ou normal, somente ativos ou todos
     * Ativos incluem vinculos externos, dependentes, etc além dos alunos e servidores normais
     * Inativos são aqueles que não tem mais vínculos com a USP
     *
     * @param String $nome Nome a ser buscado
     * @param Bool $fonetico Se true faz busca no campo nompesfon, se false faz em nompesttd
     * @param Bool $ativos Se true faz busca somente entre os ativos, se false busca em toda a tabela PESSOAS
     * @return array
     * @author Masaki K Neto, em 10/11/2020
     * @author Masaki K Neto, atualizado em 1/2/2022
     */
    public static function procurarPorNome(string $nome, bool $fonetico = true, bool $ativos = true)
    {
        if ($fonetico) {
            $nome = Uteis::fonetico($nome);
            $query_busca = "P.nompesfon like :nome";
        } else {
            $nome = trim($nome);
            $nome = Uteis::substituiAcentosParaSql($nome);
            $nome = str_replace(' ', '%', $nome);
            $query_busca = "UPPER(P.nompesttd) LIKE UPPER(:nome)";
        }

        if ($ativos) {
            $query = "SELECT P.*, L.* FROM PESSOA P
                INNER JOIN LOCALIZAPESSOA L on L.codpes = P.codpes
                WHERE L.tipdsg = NULL --exclui designações
                AND $query_busca
                ORDER BY P.nompesttd ASC";
        } else {
            $query = "SELECT P.* FROM PESSOA P
                WHERE $query_busca
                ORDER BY P.nompesttd ASC";
        }

        $param = ['nome' => '%' . $nome . '%'];
        return DB::fetchAll($query, $param);
    }

    /**
     * Método para buscar pessoas por parte do código ou do nome
     *
     * A busca é fonética, somente ativos ou todos
     * O método foi ajustado para compatibilizar com procuraPorNome() e dump()
     *
     * 9/3/2022 Revertido em parte para o método original, mas mantendo fonético
     *
     * @param String $busca Código ou Nome a ser buscado
     * @param Bool $ativos Se true faz busca somente entre os ativos, se false busca em toda a base
     * @return array
     * @author André Canale Garcia <acgarcia@sc.sp.br> // Adaptação do método procurarPorNome
     * @author Masaki K Neto, modificado em 1/2/2022
     */
    public static function procurarPorCodigoOuNome(string $busca, bool $ativos = true)
    {
        if ($ativos) {
            # se ativos vamos fazer join com LOCALIZAPESSOA
            $query = "SELECT DISTINCT P.* FROM PESSOA P, LOCALIZAPESSOA L
                WHERE (CAST(P.codpes AS NVARCHAR) LIKE :codpes OR P.nompesfon LIKE :nome)
                AND L.codpes = P.codpes
                ORDER BY P.nompes ASC";
        } else {
            $query = "SELECT DISTINCT P.* FROM PESSOA P
                WHERE (CAST(P.codpes AS NVARCHAR) LIKE :codpes OR P.nompesfon LIKE :nome)
                ORDER BY P.nompes ASC";
        }

        $param = [
            'codpes' => '%' . $busca . '%',
            'nome' => '%' . Uteis::fonetico($busca) . '%',
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Método para listar vínculos de uma pessoa
     *
     * @param Integer $codpes
     * @param Integer $codundclgi (opt)
     * @return array
     * @author Alessandro Costa de Oliveira em 04/03/2021. Bug fix para aceitar a chamada sem o código de unidade
     */
    public static function vinculos(int $codpes, int $codundclgi = 0)
    {
        $query = "SELECT * FROM LOCALIZAPESSOA
                    WHERE codpes = convert(int,:codpes)";
        if ($codundclgi != 0) {
            $query .= " AND codundclg = convert(int,:codundclgi)";
            $param['codungclgi'] = $codundclgi;
        }
        $param['codpes'] = $codpes;

        $result = DB::fetchAll($query, $param);

        $vinculos = array();
        foreach ($result as $row) {
            $vinculo = "";
            if (!empty($row['tipvinext'])) {
                $vinculo = $vinculo . $row['tipvinext'];
            }

            if (!empty($row['nomfnc'])) {
                $vinculo = $vinculo . " - " . $row['nomfnc'];
            }

            if (!empty($row['nomset'])) {
                $vinculo = $vinculo . " - " . $row['nomset'];
            }

            if (!empty($row['sglclgund'])) {
                $vinculo = $vinculo . " - " . $row['sglclgund'];
            }

            in_array($vinculo, $vinculos) ?: array_push($vinculos, trim($vinculo));
        }
        return $vinculos;
    }

    /**
     * Método para retornar servidores ativos na unidade
     *
     * O parâmetro $codundclgi passou a ser opcional pois não vai ser mais utilizado
     * foi mantido somente para compatibilidade retroativa
     *
     * @param Integer $codundclgi
     * @return array
     * @deprecated em favor de listarServidores()
     * @author Masaki K Neto atualizado em 28/1/2022
     */
    public static function servidores($codundclgi = '')
    {
        return SELF::ListarServidores();
    }

    /**
     * Retorna lista de servidores não docentes ativos na unidade
     *
     * Retorna dados das tabelas localizapessoa e pessoa
     *
     * E possivel aplicar filtros sobre qualquer coluna retornada.
     * O filtro é um array no formato [coluna => valor].
     *
     * @param array $filtros - default = []
     * @return array
     * @author Masaki K Neto atualizado em 28/1/2022
     */
    public static function listarServidores($filtros = [])
    {
        $filtros['LOCALIZAPESSOA.tipvinext'] = 'Servidor';
        $filtros['LOCALIZAPESSOA.sitatl'] = 'A';
        list($str_where, $params) = DB::criaFiltroBusca($filtros, [], []);
        if (!empty(substr($str_where, 7))) {
            $str_where = ' AND ' . substr($str_where, 7);
        }

        $query = "SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA
                    INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes)
                    WHERE LOCALIZAPESSOA.codundclg IN (" . getenv('REPLICADO_CODUNDCLG') . ")
                    {$str_where}
                    ORDER BY LOCALIZAPESSOA.nompes";

        return DB::fetchAll($query, $params);
    }

    /**
     * Método para retornar servidores designados ativos na unidade
     *
     *  Valores possíveis para categoria: 1 para Servidor ou 2 para Docente.
     *  Se for qualquer outro valor retornará todos os designados, independente do vínculo.
     *  Substitui o método designados
     *
     * @param int $categoria define o tipo de vinculo da pessoa designada.
     * @return void
     * @author @st-ricardof, em 8/2022
     */
    public static function listarDesignados(int $categoria = 0)
    {
        $codundclg = getenv('REPLICADO_CODUNDCLG');

        $query = "SELECT L.*, P.* FROM LOCALIZAPESSOA L
                    INNER JOIN PESSOA P ON (L.codpes = P.codpes)
                    WHERE (L.tipvinext = 'Servidor Designado'
                        AND L.codundclg IN ({$codundclg})
                        AND L.sitatl = 'A')
                        __tipvinext__
                    ORDER BY L.nompes";

        if ($categoria == 1 || $categoria == 2) {
            $categoria = $categoria == 1 ? 'Servidor' : 'Docente';

            $query_tipvinext = "AND L.codpes IN
                        (SELECT codpes
                        FROM LOCALIZAPESSOA L
                        WHERE L.tipvinext = '$categoria'
                        AND L.codundclg IN ({$codundclg})
                        AND L.sitatl = 'A')";

            $query = str_replace('__tipvinext__', $query_tipvinext, $query);
        } else {
            $query = str_replace('__tipvinext__', '', $query);
        }

        return DB::fetchAll($query);
    }

    /**
     * Método para retornar estagiários ativos na unidade
     *
     * @param Integer $codundclgi
     * @return array
     */
    public static function estagiarios($codundclgi)
    {
        $query = "SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA
                    INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes)
                    WHERE ( LOCALIZAPESSOA.tipvin LIKE 'ESTAGIARIORH'
                        AND LOCALIZAPESSOA.codundclg = convert(int,:codundclgi)
                        AND LOCALIZAPESSOA.sitatl = 'A')
                    ORDER BY LOCALIZAPESSOA.nompes";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Método para retornar o total dos vinculos ativos na unidade por tipo de vínculo extenso
     *
     * @param String $vinculo Tipo do vinculo por extenso (tipvinext)
     * @param Integer $codundclg
     * @return Integer
     */
    public static function totalVinculo($vinculo, $codundclg)
    {
        $query = "SELECT COUNT(codpes) FROM LOCALIZAPESSOA
                    WHERE tipvinext = :vinculo
                        AND sitatl = 'A'
                        AND codundclg = convert(int,:codundclg)";
        $param = [
            'vinculo' => $vinculo,
            'codundclg' => $codundclg,
        ];
        return DB::fetch($query, $param)['computed'];
    }

    /**
     * Método para retornar todos os tipos de vínculos por extenso (tipvinext)
     *
     * @return Array
     */
    public static function todosVinculosExtenso()
    {
        $query = "SELECT DISTINCT(tipvinext) FROM LOCALIZAPESSOA";
        return DB::fetchAll($query);
    }

    /**
     * Retorna o nome completo (nome social) a partir do codpes
     * @param type $codpes
     * @return boolean
     * @deprecated em favor de obterNome, em 20/12/2021
     */
    public static function nomeCompleto($codpes)
    {
        return SELF::obterNome($codpes);
    }

    /**
     * Recebe um codpes e retorna o nome completo (nome social)
     * ou recebe um array de codpes e retorna uma lista chaveada dos nomes (codpes->nome)
     * @param Integer|Array $codpes
     * @return String|Array
     */
    public static function obterNome($codpes)
    {
        if (is_array($codpes)) {
            $codpes = implode(',', $codpes);
        } else {
            $codpes = (int) $codpes; //se não for array, garante que seja int
        }
        $query = "SELECT codpes, nompesttd FROM PESSOA
                  WHERE codpes IN ({$codpes}) ORDER BY nompes";

        $result = DB::fetchAll($query);

        if (!is_int($codpes)) { // se for array de codpes
            $nomes = [];
            foreach ($result as $pessoa) {
                $nomes[$pessoa['codpes']] = $pessoa['nompesttd'];
            }
            return $nomes;
        } else if (!empty($result)) { // se for apenas um codpes, retornará o nome direto em string
            return $result[0]['nompesttd'];
        }

        return $result;
    }

    /**
     * Método para retornar os tipos de vínculos por extenso (tipvinext) de ativos, com base na unidade
     *
     * Somente ATIVOS: alunos regulares, tipvin IN ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'ALUNOCONVENIOINT'),
     * funcionários, estagiários e docentes, tipvin IN ('SERVIDOR', 'ESTAGIARIORH')
     * Incluido também os Docente Aposentado
     *
     * @param Integer $codundclgi
     * @return Array
     */
    public static function tiposVinculos($codundclgi)
    {
        $query = "SELECT DISTINCT tipvinext FROM LOCALIZAPESSOA
                    WHERE sitatl IN ('A', 'P')
                        AND codundclg = convert(int, :codundclgi)
                        AND (tipvin IN ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'ALUNOCONVENIOINT', 'SERVIDOR', 'ESTAGIARIORH'))
                        AND (tipvinext NOT IN ('Servidor Designado', 'Servidor Aposentado'))
                    ORDER BY tipvinext";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Método para retornar *array* com todas as pessoas ativas por vínculo
     * Somente ATIVOS (também Docente Aposentado)
     * Se o terceiro parâmetro *$contar* for igual a 1, retorna um *array*
     * com o índice *total* que corresponde ao número total de pessoas do tipo de vínculo
     *
     * @param String $vinculo
     * @param Integer $codundclgi
     * @param Integer $contar Default 0
     * @return void
     */
    public static function ativosVinculo($vinculo, $codundclgi, $contar = 0)
    {
        if ($contar == 0) {
            $colunas = "L.*, P.*";
            $ordem = "ORDER BY L.nompes";
        } else {
            $colunas = "COUNT(*) total";
            $ordem = "";
        }

        $query = "SELECT $colunas FROM LOCALIZAPESSOA L
                    INNER JOIN PESSOA P ON (L.codpes = P.codpes)
                    WHERE (L.tipvinext = :vinculo
                        AND L.codundclg = CONVERT(INT, :codundclgi)
                        AND L.sitatl IN ('A', 'P'))
                    $ordem";

        $param = [
            'codundclgi' => $codundclgi,
            'vinculo' => $vinculo,
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Método para retornar *array* com a lista de servidores (docentes, funcionários e estagiários) por setor(es)
     *
     * Se aposentados = 1, lista também os docentes aposentados (stiatl = 'P' AND tipvinext NOT IN ('Servidor Aposentado')
     *
     * @param Array $codset
     * @param Integer $aposentados (opt) Default 1
     * @return void
     * @author Alessandro Costa de Oliveira, em 10/03/2021
     */
    public static function listarServidoresSetor(array $codset, int $aposentados = 1)
    {
        // $filtro = "WHERE (L.codset IN (:setor) AND L.codfncetr = 0)"; # retira os designados
        $filtro = "WHERE (L.codset IN (" . implode(',', $codset) . ") AND L.codfncetr = 0)"; # retira os designados
        if ($aposentados == 0) {
            $filtro .= " AND (L.sitatl IN ('A'))";
        } else {
            $filtro .= " AND (L.sitatl IN ('A', 'P') AND L.tipvinext NOT IN ('Servidor Aposentado'))";
        }
        $colunas = "DISTINCT P.*";
        $ordem = "ORDER BY P.nompes";
        // $param['setor'] = implode(',', $codset); # retorna vazio para mais de um setor
        $query = "SELECT $colunas FROM PESSOA P INNER JOIN LOCALIZAPESSOA L ON (P.codpes = L.codpes) $filtro $ordem";
        // return DB::fetchAll($query, $param);
        return DB::fetchAll($query);
    }

    /**
     * Método para retornar o total de servidores (docentes, funcionários e estagiários) por setor(es)
     * Se aposentados = 1, conta também os docentes aposentados (stiatl = 'P' AND tipvinext NOT IN ('Servidor Aposentado')
     *
     * @param Array $codset
     * @param Integer $aposentados (opt) Default 1
     * @return void
     * @author Alessandro Costa de Oliveira, em 10/03/2021
     */
    public static function contarServidoresSetor(array $codset, int $aposentados = 1)
    {
        // $filtro = "WHERE (L.codset IN (:setor) AND L.codfncetr = 0)"; # retira os designados
        $filtro = "WHERE (L.codset IN (" . implode(',', $codset) . ") AND L.codfncetr = 0)"; # retira os designados
        if ($aposentados == 0) {
            $filtro .= " AND (L.sitatl IN ('A'))";
        } else {
            $filtro .= " AND (L.sitatl IN ('A', 'P') AND L.tipvinext NOT IN ('Servidor Aposentado'))";
        }
        $colunas = "COUNT(*) total";
        // $param['setor'] = implode(',', $codset); # retorna vazio para mais de um setor
        $query = "SELECT $colunas FROM PESSOA P INNER JOIN LOCALIZAPESSOA L ON (P.codpes = L.codpes) $filtro";
        // return DB::fetch($query, $param);
        return DB::fetch($query);
    }

    /**
     * Método para listar todos os vínculos e setores de uma pessoa
     *
     * Fundamental para o uspdev/web-ldap-admin
     * Somente ATIVOS
     * Também Docente Aposentado
     *
     * @param Integer $codpes
     * @param Integer (opt) $codundclgi
     * @return array
     */
    public static function vinculosSetores(int $codpes, int $codundclgi = 0)
    {
        // codfncetr = 0 não traz as linhas de registro de designados (chefias)
        $query = "SELECT * FROM LOCALIZAPESSOA WHERE codpes = CONVERT(INT, :codpes) AND sitatl IN ('A', 'P') AND codfncetr = 0";
        // Por precaução excluí funcionários aposentados
        $query .= " AND tipvinext NOT IN ('Servidor Aposentado')";
        // Somente os vínculos regulares 'ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'ALUNOCONVENIOINT', 'SERVIDOR', 'ESTAGIARIORH'
        $query .= " AND tipvin IN ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'ALUNOCONVENIOINT', 'SERVIDOR', 'ESTAGIARIORH')";
        if ($codundclgi != 0) {
            $query .= " AND codundclg = CONVERT(INT, :codundclgi)";
            $param['codundclgi'] = $codundclgi;
        }
        $param['codpes'] = $codpes;

        $result = DB::fetchAll($query, $param);

        // Inicializa o array de vínculos e setores
        $vinculosSetores = array();
        foreach ($result as $row) {
            if (!empty($row['tipvinext'])) {
                $vinculo = trim($row['tipvinext']);
                // Adiciona os vínculos por extenso
                array_push($vinculosSetores, $vinculo);
                // Adiciona o departamento quando também for Aluno de Graduação
                if (trim($row['tipvinext']) == 'Aluno de Graduação') {
                    $setorGraduacao = Graduacao::setorAluno($row['codpes'], $codundclgi)['nomabvset'];
                    array_push($vinculosSetores, $row['tipvinext'] . ' ' . $setorGraduacao);
                }
            }
            if (!empty(trim($row['nomabvset']))) {
                $setor = trim($row['nomabvset']);
                // Remove o código da unidade da sigla do setor
                $setor = str_replace('-' . $codundclgi, '', $setor);
                // Adiciona as siglas dos setores
                array_push($vinculosSetores, $setor);
                // Adiciona os vínculos por extenso concatenando a sigla do setor
                array_push($vinculosSetores, $row['tipvinext'] . ' ' . $setor);
            }
        }
        $vinculosSetores = array_unique($vinculosSetores);
        sort($vinculosSetores);

        return $vinculosSetores;
    }

    /**
     * Método para retornar data de nascimento de uma pessoa, com base no seu número USP ($codpes)
     *
     * @param Integer $codpes
     * @return void
     */
    public static function nascimento($codpes)
    {
        $result = self::dump($codpes);
        if (!empty($result)) {
            return Uteis::data_mes($result['dtanas']);
        }
        return $result;
    }

    /**
     * Método que verifica através do número USP se a pessoa tem estágio USP ou não
     * retorna true se a pessoa tiver um estágio na USP
     * ou false caso o contrário
     * Somente ATIVOS
     *
     * @param Integer $codpes
     * @return boolean
     */
    public static function verificarEstagioUSP($codpes)
    {
        $query = " SELECT codpes from LOCALIZAPESSOA
                    WHERE codpes = convert(int,:codpes)
                    AND tipvin LIKE 'ESTAGIARIORH' ";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetch($query, $param);
        if (!empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * Método para retornar o total de docentes ativos na unidade do gênero especificado
     * @param Char $sexpes
     * @return Integer
     */
    public static function contarDocentesAtivosPorGenero($sexpes)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');

        $query = " SELECT COUNT (DISTINCT LOCALIZAPESSOA.codpes) FROM LOCALIZAPESSOA
                    JOIN PESSOA ON PESSOA.codpes = LOCALIZAPESSOA.codpes
                    WHERE LOCALIZAPESSOA.tipvinext = 'Docente'
                    AND LOCALIZAPESSOA.codundclg IN ({$unidades})
                    AND PESSOA.sexpes = :sexpes AND LOCALIZAPESSOA.sitatl = 'A' ";
        $param = [
            'sexpes' => $sexpes,
        ];
        return DB::fetch($query, $param)['computed'];
    }

    /**
     * Método para retornar o total de estágiarios ativos na unidade do gênero especificado
     * @param Char $sexpes
     * @return Integer
     */
    public static function contarEstagiariosAtivosPorGenero($sexpes)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');

        $query = " SELECT COUNT (DISTINCT LOCALIZAPESSOA.codpes) FROM LOCALIZAPESSOA
                    JOIN PESSOA ON PESSOA.codpes = LOCALIZAPESSOA.codpes
                    WHERE LOCALIZAPESSOA.tipvin = 'ESTAGIARIORH'
                    AND LOCALIZAPESSOA.codundclg IN ({$unidades})
                    AND PESSOA.sexpes = :sexpes ";
        $param = [
            'sexpes' => $sexpes,
        ];
        return DB::fetch($query, $param)['computed'];
    }

    /**
     * Método que recebe o codpes e retorna os campos para o endereço completo:
     * rua/avenida, número, complemento, bairro, cidade e UF.
     * @param Integer $codpes
     * @return array
     */
    public static function obterEndereco($codpes)
    {
        $query = "SELECT TL.nomtiplgr, EP.epflgr, EP.numlgr, EP.cpllgr, EP.nombro, L.cidloc, L.sglest, EP.codendptl
                    FROM ENDPESSOA AS EP
                    JOIN LOCALIDADE AS L
                    ON EP.codloc = L.codloc
                    JOIN TIPOLOGRADOURO AS TL
                    ON EP.codtiplgr = TL.codtiplgr
                    WHERE EP.codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        return DB::fetch($query, $param);
    }

    /**
     * Método que lista docentes ativos e/ou inativos da Unidade agrupando por setor (departamento)
     *
     * Se $codset = 0 ou não for informado, buscará todos os docentes da unidade.
     * Se $sitatl = 0 ou não informado, buscará docentes ativos ($sitatl = A)
     *
     * @param $codset - lista de 'código do setor' separados por vírgula
     * @param String $sitatl - lista de 'situação atual' separados por vírgula: 'A' para ativos, 'P' para inativos ou 'A,P' para todos
     * @return Array lista de docentes com dados da tabela LOCALIZAPESSOA
     * @author Refatorado por @gabrielareisg - 30/04/2021 - issue #425
     *
     */
    public static function listarDocentes(string $codset_list = null, string $sitatl_list = 'A')
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $where_setores = $codset_list ? "AND L.codset IN ({$codset_list})" : '';

        # como sitatl_list contém letras, temos de acrescentar aspas em cada elemento da lista
        $sitatl_list = Uteis::str_putcsv(explode(',', $sitatl_list), ',', "'");

        $query = "SELECT * FROM LOCALIZAPESSOA L
            WHERE (L.tipvinext = 'Docente' OR L.tipvinext = 'Docente Aposentado')
                AND L.codundclg IN ({$unidades})
                AND L.sitatl IN ($sitatl_list)
                $where_setores
            ORDER BY L.nompes";

        return DB::fetchAll($query);
    }

    /**
     * Método para retornar o total de servidores ativos na unidade do gênero especificado
     * @param Integer $codpes
     * @return int|bool
     */
    public static function contarServidoresAtivosPorGenero($sexpes)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = " SELECT COUNT (DISTINCT LOCALIZAPESSOA.codpes) FROM LOCALIZAPESSOA
                    JOIN PESSOA ON PESSOA.codpes = LOCALIZAPESSOA.codpes
                    WHERE LOCALIZAPESSOA.tipvinext LIKE 'Servidor'
                    AND LOCALIZAPESSOA.codundclg IN ({$unidades})
                    AND PESSOA.sexpes = :sexpes ";
        $param = [
            'sexpes' => $sexpes,
        ];
        return DB::fetch($query, $param)['computed'];
    }

    /**
     * Método que recebe um email cadastrado no sistema (email usp ou alternativo) e
     * retorna o número USP da pessoa
     * @param String
     * @return boolean
     */
    public static function obterCodpesPorEmail($codema)
    {
        $query = " SELECT codpes FROM EMAILPESSOA
                    WHERE EMAILPESSOA.codema = :codema";
        $param = [
            'codema' => $codema,
        ];
        $result = DB::fetch($query, $param);
        if ($result) {
            return $result['codpes'];
        }

        return '';
    }

    /**
     * Método que dado um número USP retorna um ramal USP
     * @param integer $codpes
     * @return string
     */
    public static function obterRamalUsp(int $codpes)
    {
        $query = " SELECT numtelfmt
                    FROM LOCALIZAPESSOA
                    WHERE LOCALIZAPESSOA.codpes = convert(int, :codpes)";
        $param = [
            'codpes' => $codpes,
        ];

        $result = DB::fetch($query, $param);

        if (!empty($result)) {
            return $result['numtelfmt'];
        }

        return "";
    }

    /**
     * Método que lista docentes aposentados Sênior (em atividade) de uma unidade por setor (departamento) solicitado
     *
     * $codset pode ser um número (para um único setor) ou pode ser
     * uma lista de setores separados por vírgula (para um ou mais de um setores)
     * Se não informado, listará de todos os setores.
     *
     * @param List $codset (opt) - Código do setor
     * @return array
     */
    public static function listarDocentesAposentadosSenior($codset = false)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $current = date("Y-m-d H:i:s");
        $addquery = '';
        if ($codset) {
            $addquery = "AND L.codset IN ({$codset})";
        }
        $query = "SELECT * FROM LOCALIZAPESSOA L
            INNER JOIN VINCSATPROFSENIOR V ON (L.codpes = V.codpes)
            WHERE (
                L.tipvinext = 'Docente Aposentado'
                AND L.sitatl = 'P'
                AND V.codund IN ({$unidades})
                AND V.dtafimcbd > '{$current}'
                $addquery
                )
            ORDER BY L.nompes";

        return DB::fetchAll($query);
    }

    /**
     * Método para retornar o codcur e o nome do curso da pessoa através do codpes
     *
     * @return array
     */
    public static function retornarCursoPorCodpes($codpes)
    {
        $query = DB::getQuery('Pessoa.retornarCursoPorCodpes.sql');

        $param = [
            'codpes' => $codpes,
        ];

        $result = DB::fetchAll($query, $param);
        return empty($result) ? null : $result[0];
    }

    /**
     * Método que recebe um período (dtaini, dtafim) para listar falecidos.
     * Data no formato americano AAAA-MM-DD
     * @return array
     */
    public static function listarFalecidosPorPeriodo($dtaini, $dtafim)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = DB::getQuery('Pessoa.listarFalecidosPorPeriodo.sql');

        $query = str_replace('__unidades__', $unidades, $query);

        $param = [
            'dtaini' => $dtaini,
            'dtafim' => $dtafim,
        ];

        return DB::fetchAll($query, $param);
    }

    /**
     * Método que retorna siglas dos vínculos ativos de uma pessoa
     *
     * @param Integer $codpes
     * @return array
     * @author @thiagogomesverissimo em 25/06/2021
     */
    public static function obterSiglasVinculosAtivos(int $codpes)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = DB::getQuery('Pessoa.obterSiglasVinculosAtivos.sql');
        $query = str_replace('__unidades__', $unidades, $query);

        $param = [
            'codpes' => $codpes,
        ];

        $return = DB::fetch($query, $param);
        if ($return) {
            return array_values($return);
        }

        return null;
    }

    /**
     * Método que retorna siglas dos setores ativos de uma pessoa
     *
     * @param Integer $codpes
     * @return array
     * @author @thiagogomesverissimo em 25/06/2021
     */
    public static function obterSiglasSetoresAtivos(int $codpes)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = DB::getQuery('Pessoa.obterSiglasSetoresAtivos.sql');
        $query = str_replace('__unidades__', $unidades, $query);

        $param = [
            'codpes' => $codpes,
        ];

        $return = DB::fetch($query, $param);
        if ($return) {
            return array_values($return);
        }

        return null;
    }

    /**
     * Método que retorna email usp
     *
     * @param Integer $codpes
     * @return String
     * @author @thiagogomesverissimo em 25/06/2021
     */
    public static function retornarEmailUsp(int $codpes)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = DB::getQuery('Pessoa.retornarEmailUsp.sql');

        $param = [
            'codpes' => $codpes,
        ];

        $return = DB::fetch($query, $param);
        if ($return) {
            return $return['codema'];
        }

        return null;
    }

    /**
     * Método que facilita pegar o nome do colegiado dado seu código e sigla
     *
     * @param Integer $codclg código do colegiado pode ser obtido com listarColegiados()
     * @param String $sglclg sigla do colegiado, também pode ser obtido com listarColegiados()
     * @return String nome do colegiado
     * @author @thiagogomesverissimo - 23/11/2021
     *
     */
    public static function retornarNomeColegiado(int $codclg, string $sglclg)
    {
        $query = DB::getQuery('Pessoa.retornarNomeColegiado.sql');

        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = str_replace('__unidades__', $unidades, $query);

        $param = [
            'codclg' => $codclg,
            'sglclg' => $sglclg,
        ];

        $return = DB::fetch($query, $param);
        if ($return) {
            return $return['nomclg'];
        }

        return '';
    }

    /**
     * Método que lista colegiados que possuem membros ativos.
     *
     * Não foi usada a tabela COLEGIADO apenas pois não há informações
     * de quando o colegiado é desativado. Apesar de existir um campo indicando
     * que o colegiado foi desativado ele nunca é preenchido, então só sabemos
     * que o colegiado foi desativado quando ele não tem mais membros
     *
     * @return Array lista de colegiados
     * @author @thiagogomesverissimo - 23/11/2021
     *
     */
    public static function listarColegiados()
    {
        $query = DB::getQuery('Pessoa.listarColegiados.sql');

        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = str_replace('__unidades__', $unidades, $query);

        $dtafimmdt = Date('Y-m-d') . ' 00:00:00';

        $param = [
            'dtafimmdt' => $dtafimmdt,
        ];

        return DB::fetchAll($query, $param);
    }

    /**
     * Método que lista membros titulares e suplentes para um dado colegiado
     *
     * @param Integer $codclg código do colegiado pode ser obtido com listarColegiados()
     * @param String $sglclg sigla do colegiado, também pode ser obtido com listarColegiados()
     * @return Array lista de membros do colegiado selecioando
     * @author @thiagogomesverissimo - 23/11/2021
     *
     */
    public static function listarTitularesSuplentesDoColegiado(int $codclg, string $sglclg)
    {
        $query = DB::getQuery('Pessoa.listarTitularesSuplentesDoColegiado.sql');

        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = str_replace('__unidades__', $unidades, $query);

        $dtafimmdt = Date('Y-m-d') . ' 00:00:00';

        $param = [
            'dtafimmdt' => $dtafimmdt,
            'codclg' => $codclg,
            'sglclg' => $sglclg,
        ];

        $result = DB::fetchAll($query, $param);

        if ($result) {
            $codpes_membros = array_merge(array_column($result, 'titular'), array_column($result, 'suplente'));
            $codpes_membros = array_map('intval', array_filter($codpes_membros)); //removendo valores vazios e convertendo str para int
            $nomes_membros = Pessoa::obterNome($codpes_membros);
            $membros = [];
            foreach ($result as $membro) {

                $membro['nome_titular'] = $nomes_membros[(int) $membro['titular']];
                $membro['nome_suplente'] = empty($membro['suplente']) ? '' : $nomes_membros[(int) $membro['suplente']];
                $membros[] = $membro;
            }
            usort($membros, function ($a, $b) {
                return strcmp($a["nome_titular"], $b["nome_titular"]);
            });
            return $membros;
        }
        return [];
    }

    /*
     * Método para listar mais informações de servidores ativos
     *
     * Usado em uspdev/pessoas
     * @param string $tipvinext valores possíveis 'Servidor', 'Docente' ou 'Docente Aposentado'
     * @return array
     * @author @alecostaweb em 12/11/2021 issue #478
     */
    public static function listarMaisInformacoesServidores(string $tipvinext)
    {
        $codundclg = getenv('REPLICADO_CODUNDCLG');
        // Para o caso da pessoa ter mais de um vínculo ativo como funcionário e também como docente
        if ($tipvinext == 'Servidor') {
            $condicao = "AND (V.tipmer IS NULL)";
        } else {
            $condicao = "AND (V.tipmer IS NOT NULL)";
        }
        $query = "SELECT DISTINCT P.codpes, P.nompesttd, P.sexpes, P.dtanas, P.dtanas,
                L.tipvin, L.tipvinext, L.dtainivin, L.dtainivin, L.codset, L.nomabvset,
                L.nomset, V.nomabvfnc, L.nomfnc, V.tipfnc, V.dtainisitfun, L.nomloc,
                L.epflgrund, L.numtelfmt, codema, D.idfpescpq, E.nomesc, E.nivesc,
                GF.dscgrufor, V.tipcon, V.nomcaa, V.nomabvcla, V.nivgrupvm, V.tipjor, V.tipmer
            FROM PESSOA P
                INNER JOIN LOCALIZAPESSOA L ON P.codpes = L.codpes
                INNER JOIN VINCULOPESSOAUSP V ON P.codpes = V.codpes
                INNER JOIN ESCOLARIDADE AS E ON V.codesc = E.codesc
                LEFT OUTER JOIN DIM_PESSOA_XMLUSP D ON P.codpes = D.codpes
                LEFT OUTER JOIN TABGRAUFORM AS GF ON V.grufor = GF.grufor
            WHERE (L.tipvinext = :tipvinext)
                AND (L.codundclg IN ($codundclg))
                AND (V.dtainisitfun IS NOT NULL) $condicao
            ORDER BY P.nompesttd";
        $param = [
            'tipvinext' => $tipvinext,
        ];
        return DB::fetchAll($query, $param);
    }

    /********** Métodos deprecados que devem ser eliminados numa futura major release ***********/

    /**
     * Método para buscar pessoas por nome ou parte do nome, recebe uma string nome e retorna os resultados para a tabela Pessoa
     *
     * @param string $nome
     * @return array
     * @deprecated em favor de procurarPorNome, em 10/11/2020
     */
    public static function nome($nome)
    {
        return SELF::procurarPorNome($nome, false, false);
    }

    /**
     * Método para buscar pessoas por nomes fonéticos
     *
     * @param string $nome
     * @return void
     * @deprecated em favor de procurarPorNome, em 10/11/2020
     */
    public static function nomeFonetico($nome)
    {
        return SELF::procurarPorNome($nome, true, false);
    }

    /**
     * Método que lista siglas dos vínculos ativos de uma pessoa, em uma dada unidade
     *
     * @param Integer $codpes
     * @param Integer (opt) $codundclgi
     * @return array
     * @author Alessandro Costa de Oliveira em 04/03/2021. Bug fix para aceitar a chamada sem o código de unidade
     * @deprecated em favor de obterSiglasVinculosAtivos, em 25/06/2021 - @thiagogomesverissimo
     */
    public static function vinculosSiglas(int $codpes, int $codundclgi = 0)
    {
        return SELF::obterSiglasVinculosAtivos($codpes);
    }

    /**
     * Método para retornar siglas dos setores que uma pessoa tem vínculo
     *
     * @param Integer $codpes
     * @param Integer $codundclgi
     * @return array
     * @author Alessandro Costa de Oliveira em 04/03/2021. Bug fix para aceitar a chamada sem o código de unidade
     * @deprecated em favor de obterSiglasSetoresAtivos, em 25/06/2021 - @thiagogomesverissimo
     */
    public static function setoresSiglas(int $codpes, int $codundclgi = 0)
    {
        return SELF::obterSiglasSetoresAtivos($codpes);
    }

    /**
     * (deprecated) Método que recebe número USP para retornar email USP da pessoa
     *
     * @param Integer $codpes
     * @return String
     * @deprecated em favor de retornarEmailUsp, em 25/06/2021 - @thiagogomesverissimo
     */
    public static function emailusp($codpes)
    {
        return SELF::retornarEmailUsp($codpes);
    }

    /**
     * (deprecated) Método para retornar servidores designados ativos na unidade
     *
     * @param Integer $codundclgi. O valor deste parâmetro não está mais sendo utilizado, o código da unidade agora é obtido através da configuração no .env
     * @return Array
     * @deprecated em favor de listarDesignados, em 22/07/2021 - @st-ricardof.
     */
    public static function designados($codundclgi)
    {
        return self::listarDesignados();
    }

}
