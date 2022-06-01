<?php

namespace Uspdev\Replicado;

class Posgraduacao
{
    /**
     * Verifica se aluno (codpes) tem matrícula ativa na pós-graduação da unidade
     *
     * @param int $codpes Código da pessoa
     * @param int $codundclgi Código da unidade
     *
     * @return bool
     */
    public static function verifica($codpes, $codundclgi)
    {
        $query = " SELECT * FROM LOCALIZAPESSOA WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetchAll($query, $param);

        if (!empty($result)) {
            foreach ($result as $row) {
                if (trim($row['tipvin']) == 'ALUNOPOS' && trim($row['sitatl']) == 'A' && trim($row['codundclg']) == $codundclgi) {
                    return true;
                }

            }
        }
        return false;
    }

    /**
     * Retorna *array* de todos alunos de pós-graduação ativos na unidade
     *
     * @param int $codundclgi Código da unidade
     *
     * @return array
     */
    public static function ativos($codundclgi)
    {
        $query = "SELECT LOCALIZAPESSOA.*, PESSOA.* FROM LOCALIZAPESSOA";
        $query .= " INNER JOIN PESSOA ON (LOCALIZAPESSOA.codpes = PESSOA.codpes)";
        $query .= " WHERE LOCALIZAPESSOA.tipvin = 'ALUNOPOS' AND LOCALIZAPESSOA.codundclg = convert(int, :codundclgi) AND LOCALIZAPESSOA.sitatl = 'A'";
        $query .= " ORDER BY PESSOA.nompes ASC ";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Retorna *array* dos programas de pós-graduação da unidade
     *
     * Quando informado o código do curso/programa retorna somente os dados do programa solicitado
     *
     * @param int $codundclgi Código da unidade
     * @param int $codcur Código do curso
     *
     * @return array
     */
    public static function programas($codundclgi = null, $codcur = null, $codare = null)
    {
        if (!$codundclgi) {
            $codundclgi = getenv('REPLICADO_CODUNDCLG');
        }

        $query = "SELECT C.codcur, NC.nomcur, A.codare, N.nomare
                  FROM CURSO AS C
                  INNER JOIN NOMECURSO AS NC ON C.codcur = NC.codcur
                  INNER JOIN AREA AS A ON C.codcur = A.codcur
                  INNER JOIN NOMEAREA AS N ON A.codare = N.codare
                  WHERE (C.codclg IN ({$codundclgi}))
                  AND (C.tipcur = 'POS')
                  AND (N.dtafimare IS NULL)
                  AND (C.dtainiccp IS NOT NULL)
                  AND (NC.dtafimcur IS NULL)
                  ";

        //$param = ['codundclgi' => $codundclgi];
        $param = [];
        if (!is_null($codcur)) {
            $param['codcur'] = $codcur;
            $query .= " AND (C.codcur = CONVERT(INT, :codcur))";
        }
        if (!is_null($codare)) {
            $param['codare'] = $codare;
            $query .= " AND (A.codare = CONVERT(INT, :codare))";
        }
        $query .= " ORDER BY NC.nomcur ASC ";
        return DB::fetchAll($query, $param);
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
        $query .= " AND r.codare = CONVERT(int, :codare)";
        $query .= " AND r.dtavalfim > GETDATE()";
        $query .= " GROUP BY r.codpes";
        $query .= " ORDER BY nompes ASC";

        $param = ['codare' => $codare];

        return DB::fetchAll($query, $param);
    }

    /**
     * Retorna *array* do catálogo das disciplinas pertencentes à área de concentração.
     *
     * @param  int $codare Código da área de concentração pertencente a um programa de pós.
     *
     * @return array
     */
    public static function catalogoDisciplinas($codare)
    {
        $query = "SELECT DISTINCT r.sgldis, d.nomdis, r.numseqdis, r.dtaatvdis";
        $query .= " FROM R27DISMINCRE AS r, DISCIPLINA AS d";
        $query .= " WHERE d.sgldis = r.sgldis";
        $query .= " AND d.numseqdis = r.numseqdis";
        $query .= " AND r.codare = convert(int,:codare)";
        $query .= " AND (r.dtadtvdis IS NULL OR r.dtadtvdis > getdate())"; // não está desativado
        $query .= " AND d.dtaatvdis IS NOT NULL"; // está ativado
        $query .= " AND dateadd(yy,5,d.dtaatvdis)>=getdate()"; // disciplina mais nova que 5 anos
        $query .= " ORDER BY d.nomdis ASC";

        $param = ['codare' => $codare];

        return DB::fetchAll($query, $param);
    }

    /**
     * Retorna *array* contendo todos os dados da disciplina indentificada por sua sigla - sgldis.
     *
     * @param  int $sgldis Código da área de concentração pertencente a um programa de pós.
     *
     * @return array
     */
    public static function disciplina($sgldis)
    {
        $query = "SELECT TOP 1 * FROM DISCIPLINA";
        $query .= " WHERE sgldis = :sgldis";
        $query .= " ORDER BY numseqdis DESC";

        $param = ['sgldis' => $sgldis];

        return DB::fetch($query, $param);
    }

    /**
     * Retorna a lista de disciplinas em oferecimento de uma determinada área de concentração.
     *
     * Uma mesma disciplina pode ser oferecida para mais de uma área
     * Modificado em 26/1/2022 de forma a não usar mais as datas de inicio/fim.
     * Agora usa as informações da tabela R27DISMINCRE
     * 
     * @param int $codare Código da áreada PG.
     * @return array
     *
     * @author Masaki K Neto em 2020
     * @author Masaki K Neto, modificado em 3/2/2021
     * @author Masaki K Neto, modificado em 26/1/2022
     */
    public static function disciplinasOferecimento(int $codare)
    {
        $query = "SELECT d.nomdis, d.numcretotdis, o.*
            FROM OFERECIMENTO o
            INNER JOIN (
                SELECT MAX(numofe) numofe, sgldis, numseqdis FROM OFERECIMENTO
                WHERE sgldis in (
                    SELECT DISTINCT(sgldis) FROM R27DISMINCRE
                    WHERE codare=CONVERT(INT,:codare) AND dtadtvdis is NULL AND dtaatvdis is NOT NULL
                )
                GROUP BY sgldis, numseqdis
            ) tb on tb.numofe = o.numofe AND tb.sgldis = o.sgldis and tb.numseqdis = o.numseqdis
            INNER JOIN DISCIPLINA d on d.sgldis = o.sgldis AND d.numseqdis = o.numseqdis
            WHERE o.stacslofe IS NULL --status-consolidacao-oferecimento
                AND o.stacslatm IS NULL --status-consolidacao-automatica
                AND o.dtacantur IS NULL --data-cancelamento-turma
                AND o.dtafimofe > GETDATE() --data final futura
            ORDER BY o.sgldis";
        /* 
            -self join com oferecimento para pegar somente as disciplinas listadas em R27DISMINCRE da área
            -data final futura exclui algumas disciplinas perdidas em OFERECIMENTO 
            */

        $param = ['codare' => $codare];

        return DB::fetchAll($query, $param);
    }

    /**
     * Retorna dados de um oferecimento de disciplina incluindo local e ministrante.
     *
     * Local e minsitrante podem ser mais de um então são retornados na forma de array.
     * Link exemplo do Janus: https://uspdigital.usp.br/janus/componente/disciplinasOferecidasInicial.jsf?action=4&sgldis=SHS5952&ofe=1
     *
     * @param  string $sgldis Sigla da disciplina (AAA0000)
     * @param  int $numofe Número do oferecimento
     *
     * @return array
     */
    public static function oferecimento(string $sgldis, int $numofe)
    {
        $query = "SELECT o.*, d.nomdis, d.numcretotdis
           FROM OFERECIMENTO as o, DISCIPLINA as d
           WHERE o.sgldis = d.sgldis
           AND o.numseqdis = d.numseqdis
           AND o.sgldis = :sgldis
           AND o.numofe = convert(int, :numofe)
           AND o.numseqdis = (SELECT MAX(numseqdis) FROM OFERECIMENTO WHERE sgldis = :sgldis AND numofe = convert(int, :numofe))
        ";
        $param = [
            'sgldis' => $sgldis,
            'numofe' => $numofe,
        ];

        $result = DB::fetch($query, $param);
        $result['espacoturma'] = self::espacoturma($result['sgldis'], $result['numseqdis'], $result['numofe']);
        $result['ministrante'] = self::ministrante($result['sgldis'], $result['numseqdis'], $result['numofe']);

        // Tratamento das datas no formato d/m/Y
        $result['dtainiofe'] = Uteis::data_mes($result['dtainiofe']);
        $result['dtafimofe'] = Uteis::data_mes($result['dtafimofe']);
        $result['dtalimcan'] = Uteis::data_mes($result['dtalimcan']);

        // Conversão codlin para nome completo do idioma
        if (isset($result['codlinofe']) && (!empty($result['codlinofe']))) {
            $result['codlinofe'] = self::idiomaDisciplina($result['codlinofe']);
        }
        return $result;
    }

    /**
     * Retorna local e horário dos oferecimentos da disciplina.
     *
     * É usado no contexto do oferecimento.
     * O nome desse método reflete o nome da tabela no BD.
     *
     * @param  string $sgldis Sigla da disciplina
     * @param  int $numseqdis Número de sequência
     * @param  int $numofe Número do oferecimento
     *
     * @return array
     */
    public static function espacoturma(string $sgldis, int $numseqdis, int $numofe)
    {
        $query = "SELECT *
            FROM ESPACOTURMA
            WHERE sgldis = :sgldis
            AND numseqdis = convert(int, :numseqdis)
            AND numofe = convert(int, :numofe)
        ";
        $param = [
            'sgldis' => $sgldis,
            'numseqdis' => $numseqdis,
            'numofe' => $numofe,
        ];
        $result = DB::fetchAll($query, $param);
        if ($result && (!empty($result))) {
            // Percorre todos os dias que a disciplina é ministrada
            foreach ($result as $key => $dados) {
                $result[$key]['diasmnofe'] = Uteis::dia_semana($dados['diasmnofe']);
                $result[$key]['horiniofe'] = Uteis::horario_formatado($dados['horiniofe']);
                $result[$key]['horfimofe'] = Uteis::horario_formatado($dados['horfimofe']);
            }
        }
        return $result;
    }

    /**
     * Retorna lista de ministrantes da disciplina.
     *
     * É usado no contexto do oferecimento.
     *
     * @param  string $sgldis
     * @param  int $numseqdis
     * @param  int $numofe
     *
     * @return array
     */
    public static function ministrante(string $sgldis, int $numseqdis, int $numofe)
    {
        $query = "SELECT r.codpes, p.nompes FROM R32TURMINDOC AS r, PESSOA AS p
        WHERE r.codpes = p.codpes
        AND sgldis = :sgldis
        AND numseqdis = convert(int, :numseqdis)
        AND numofe = convert(int, :numofe)
        ORDER BY p.nompes ASC";
        $param = [
            'sgldis' => $sgldis,
            'numseqdis' => $numseqdis,
            'numofe' => $numofe,
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Retorna as áreas de concentração ativas dos programas de pós-graduação da unidade.
     * Se informado o código do curso (programa), retorna apenas as áreas deste curso.
     *
     * @param int $codundclgi - código da Unidade
     * @param int $codcur - código do curso de pós-graduação
     * @return type
     *
     * por Erickson Zanon - czanon@usp.br
     */
    public static function areasProgramas(int $codundclgi = null, int $codcur = null)
    {
        if (!$codundclgi) {
            $codundclgi = getenv('REPLICADO_CODUNDCLG');
        }

        //obtém programas
        $programas = Posgraduacao::programas($codundclgi, $codcur);
        // loop sobre programas obtendos suas áreas
        $programasAreas = array();
        foreach ($programas as $p) {
            $codcur = $p['codcur'];
            $query = "SELECT codare FROM AREA WHERE codcur = convert(int, :codcur)";
            $param = [
                'codcur' => $codcur,
            ];
            $codAreas = DB::fetchAll($query, $param);
            $i = 0;
            foreach ($codAreas as $a) {
                $codare = $a['codare'];
                $query = "SELECT TOP 1 N.codcur,N.codare,N.nomare ";
                $query .= " FROM NOMEAREA as N";
                $query .= " INNER JOIN CREDAREA as C ";
                $query .= " ON N.codare = C.codare";
                $query .= " WHERE N.codare = convert(int, :codare)";
                $query .= " AND C.dtadtvare IS NULL";
                $param = [
                    'codare' => $codare,
                ];
                $areas = DB::fetchAll($query, $param);
                if (empty($areas)) {
                    continue;
                }

                $nomare = $areas[0]['nomare'];

                $programasAreas[$codcur][$i]['codare'] = $codare;
                $programasAreas[$codcur][$i]['nomare'] = $nomare;
                $i++;
            }

        }
        return $programasAreas;
    }

    /**
     * Retorna os alunos de um programa (codcur) de pós
     *  da unidade (codundclgi),
     *  indexados pela área (codare).
     * Se codare não foi determinado, busca todas as áreas do programa.
     *
     * @param Int $codundclgi - código da unidade
     * @param Int $codcur - código do curso/programa
     * @param Int $codare - código da área (opcional)
     * @return Array
     */
    public static function alunosPrograma(int $codundclgi, int $codcur, int $codare = null)
    {
        // se $codare é null, seleciona todas
        if (!$codare) {
            // obtém áreas do programa
            $areasPrograma = Posgraduacao::areasProgramas($codundclgi, $codcur);
            foreach ($areasPrograma[$codcur] as $area) {
                $codares[] = $area['codare'];
            }
        } else {
            $codares[] = $codare;
        }

        $alunosPrograma = array();
        // loop sobre as áreas
        foreach ($codares as $codare) {
            $alunosArea = array();
            $query = "SELECT DISTINCT V.codare,V.codpes,L.nompes,V.nivpgm,L.codema, V.dtainivin
                        FROM VINCULOPESSOAUSP as V
                        INNER JOIN LOCALIZAPESSOA as L
                        ON (V.codpes = L.codpes)
                        WHERE V.tipvin = 'ALUNOPOS'
                         AND V.sitatl = 'A'
                         AND L.codundclg = convert(int, :codundclgi)
                         AND V.codare = convert(int, :codare)
                        ORDER BY L.nompes ASC";
            $param = [
                'codundclgi' => $codundclgi,
                'codare' => $codare,
            ];
            $alunosArea = DB::fetchAll($query, $param);
            $alunosPrograma = array_merge($alunosPrograma, $alunosArea);
        }
        return $alunosPrograma;
    }

    /**
     * Retorna nome completo do idioma da disciplina
     *
     * É usado no contexto do oferecimento.
     *
     * @param String $codlinofe
     *
     * @return string
     */
    public static function idiomaDisciplina($codlinofe)
    {
        if (isset($codlinofe) && (!empty($codlinofe))) {
            $query = "SELECT dsclin
                        FROM IDIOMA
                        WHERE codlin = :codlinofe";

            $param = [
                'codlinofe' => $codlinofe,
            ];

            $result = DB::fetchAll($query, $param);

            // Se for encontrado o nome do idioma, retornará apenas um registro
            // então já devolve apenas o campo do nome na posição 0
            return $result[0]['dsclin'];
        }
    }

    /**
     * Retorna lista de alunos que defenderam pós-graduação em determinada área
     *
     * @param  Int $codare - código da área do programa de pós graduação
     *
     * @return Array
     */
    public static function egressosArea(int $codare)
    {
        // se não fizer join com TRABALHOPROG retornou um resultado menor que deveria (codare=18134)
        $query = DB::getQuery('Posgraduacao.egressosArea.sql');
        $param = [
            'codare' => $codare,
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Retorna lista de alunos que defenderam pós-graduação em determinada área
     *
     * @author Thiago Gomes Veríssimo <thiago.verissimo@usp.br>
     * @author Gabriela dos Reis Silva <gabrielareisg@usp.br>
     *
     * @param  Int $codare - código da área do programa de pós graduação
     * @return Array
     */
    public static function contarEgressosAreaAgrupadoPorAno(int $codare)
    {
        // se não fizer join com TRABALHOPROG retornou um resultado menor que deveria (codare=18134)
        $query = DB::getQuery('Posgraduacao.contarEgressosArea.sql');
        $param = [
            'codare' => $codare,
        ];
        $result = DB::fetchAll($query, $param);
        if ($result) {
            return array_column($result, 'quantidade', 'ano');
        }
        return $result;
    }

    /**
     * Método para retornar o total de alunos de Pós Graduação matriculados, de acordo com o nível do programa, na unidade
     *
     * @param Integer $codundclg
     * @param String $nivpgm Pode ser ME, DO ou DD
     * @param Integer $codundclg (opt)
     * @return Integer
     */
    public static function totalPosNivelPrograma($nivpgm, $codundclg)
    {
        $query = "SELECT COUNT(lp.codpes) FROM LOCALIZAPESSOA AS lp
                    INNER JOIN VINCULOPESSOAUSP AS vpu
                    ON(lp.codpes = vpu.codpes AND lp.tipvin = vpu.tipvin)
                    WHERE lp.tipvin='ALUNOPOS'
                        AND lp.codundclg= convert(int,:codundclg)
                        AND lp.sitatl='A'
                        AND vpu.nivpgm=:nivpgm";

        $param = [
            'nivpgm' => $nivpgm,
            'codundclg' => $codundclg,
        ];
        return DB::fetch($query, $param)['computed'];
    }

    /**
     * Método para retornar quantidade alunos de pós-graduação em uma área (codare)
     *
     * Se $codare não for informado irá retornar a quantidade de alunos de pós-graduação de todos os programas.
     * Se for informado, irá retornar a quantidade de alunos de pós-graduação somente da área
     *
     * @param Integer $codare (optional) - código da área pertencente a um programa de pós.
     * @return void
     */
    public static function contarAtivos($codare = null)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = DB::getQuery('Posgraduacao.contarAtivos.sql');
        $query = str_replace('__unidades__', $unidades, $query);

        $param = [];
        if (!is_null($codare)) {
            $param['codare'] = $codare;
            $query .= " AND (h.codare = CONVERT(INT, :codare))";
        }
        return DB::fetch($query, $param)['computed'];
    }

    /**
     * Método para retornar quantidade alunos de pós-graduação do gênero
     * e programa (opcional) especificado
     *
     * Se $codare não for informado, irá retornar a quantidade de alunos de pós-graduação de todos os programas, do gênero especificado.
     * Se for informado, irá retornar a quantidade de alunos de pós-graduação somente do programa e do do gênero especificado.
     *
     * @param Char $sexpes
     * @param Integer $codare (optional) - código da área pertencente a um programa de pós.
     * @return void
     */
    public static function contarAtivosPorGenero($sexpes, $codare = null)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');

        $query = " SELECT COUNT(DISTINCT l.codpes) FROM LOCALIZAPESSOA l
                    JOIN PESSOA p ON p.codpes = l.codpes
                    JOIN HISTPROGRAMA h ON h.codpes = l.codpes
                    WHERE l.tipvin = 'ALUNOPOS'
                    AND l.codundclg IN ({$unidades})
                    AND p.sexpes = :sexpes";
        $param = [
            'sexpes' => $sexpes,
        ];
        if (!is_null($codare)) {
            $param['codare'] = $codare;
            $query .= " AND (h.codare = CONVERT(INT, :codare))";
        }
        return DB::fetch($query, $param)['computed'];
    }

    /*
     * Método que verifica através do número USP e código da unidade
     * se a pessoa é Ex-Aluna de Pós-Graduação ou não
     * retorna true se a pessoa for Ex-Aluna de Pós-Graduação USP
     * ou false, caso o contrário
     *
     * @param Integer $codpes : Número USP
     * @param Integer $codorg : Código da unidade
     * @return boolean
     */
    public static function verificarExAlunoPos($codpes, $codorg)
    {
        $query = " SELECT codpes from TITULOPES
                    WHERE codpes = convert(int,:codpes)
                    AND codcurpgr IS NOT NULL
                    AND codorg = convert(int,:codorg) ";
        $param = [
            'codpes' => $codpes,
            'codorg' => $codorg,
        ];
        $result = DB::fetch($query, $param);
        if (!empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * Retorna os membros da banca de um discente
     *
     * Pode-se especificar ou não o programa ou o número sequencial
     * @param Integer $codpes : Número USP
     * @param Integer $codare : Código da programa de Pós
     * @param Integer $numseqpgm : Número sequencial em que o maior indica último vínculo
     * @return array|boolean
     * @author refatorado por masakik, 5/5/2021, issue #431
     **/
    public static function listarMembrosBanca($codpes, $codare = null, $numseqpgm = null)
    {

        $query = "SELECT nompesttd = (SELECT nompesttd FROM PESSOA p WHERE p.codpes = r.codpesdct)
                  , r.*
                  FROM R48PGMTRBDOC r
                  WHERE r.codpes = convert(int, :codpes)";

        $param['codpes'] = $codpes;

        if ($codare) {
            $query .= " AND r.codare = convert(int, :codare)";
            $param['codare'] = $codare;
        }

        if ($numseqpgm) {
            $query .= " AND r.numseqpgm = convert(int, :numseqpgm)";
            $param['numseqpgm'] = $numseqpgm;
        }

        return DB::fetchAll($query, $param);
    }

    /**
     * Deprecado. O método foi renomeado para listarOrientandosAtivos.
     *
     * @author Masaki K Neto, em 6/4/2021
     */
    public static function obterOrientandosAtivos($codpes)
    {
        return SELF::listarOrientandosAtivos($codpes);
    }

    /**
     * Retorna lista de orientandos ativos de um docente (orientador)
     *
     * retorna o número USP, nome, nível (ME-mestrado, DO-doutorado ou DD-doutorado direto),
     * nome da área, data início do vínculo (não da orientação)
     *
     * Refatorado em 22/7/2021 para adequar à alteração do método obterVinculoAtivo()
     *
     * @param  Int $codpes: Número USP do docente (orientador)
     * @return Array
     * @author Refatorado por Masaki K Neto em 6/4/2021
     * @author Refatorado por @gabrielareisg em 30/4/2021 - issue #424
     * @author refatorado por masakik, em 22/7/2021
     **/
    public static function listarOrientandosAtivos(int $codpes)
    {
        $query = DB::getQuery('Posgraduacao.listarOrientandosAtivos.sql');
        $param['codpes'] = $codpes;
        $orientandos = DB::fetchAll($query, $param);

        # O foreach foi utilizado para evitar o uso de vários inner joins,
        # o que deixaria a performance do método lenta.
        foreach ($orientandos as &$orientando) {
            $vinculo = SELF::obterVinculoAtivo($orientando['codpes']);

            // vamos mergear somente alguns campos de $vinculo
            if (is_array($vinculo) && count($vinculo)) {
                foreach (['nompes', 'nivpgm', 'dtainivin', 'nomare'] as $key) {
                    $orientando[$key] = $vinculo[$key];
                }
            }
        }

        # Ordenação por nome
        usort($orientandos, function ($a, $b) {
            if (isset($b['nompes']) && isset($a['nompes'])) {
                return $b['nompes'] < $a['nompes'] ? 1 : -1;
            } else {
                return 1;
            }

        });

        return $orientandos;
    }

    /**
     * Retornar dados do vínculo ativo do aluno de Aluno de Pós Graduação
     *
     * Modificado em 22/7/2021 para retornar mais informações incluindo o orientador
     *
     * @param Int $codpes: Número USP do aluno
     * @return Array
     * @author @gabrielareisg em 30/04/2021 - #issue424
     * @author @masakik, modificado em 22/7/2021
     */
    public static function obterVinculoAtivo(int $codpes)
    {
        $query = DB::getQuery('Posgraduacao.obterVinculoAtivo.sql');
        $param['codpes'] = $codpes;
        return DB::fetch($query, $param);
    }

    /**
     * Deprecado. O método foi renomeado para listarOrientandosAtivos.
     *
     * @author Masaki K Neto, em 6/4/2021
     */
    public static function obterOrientandosConcluidos($codpes)
    {
        return SELF::listarOrientandosConcluidos($codpes);
    }

    /**
     * Retorna lista de orientandos que já concluíram seus programas a partir do número USP do orientador
     *
     * Retorna o número USP, nome, nível (ME, DO, DD), nome da área e data de defesa.
     *
     * @param Int $codpes: Número USP do docente (orientador).
     * @return Array
     * @author Refatorado por Masaki K Neto em 6/4/2021
     **/
    public static function listarOrientandosConcluidos($codpes)
    {
        $query = DB::getQuery('Posgraduacao.listarOrientandosConcluidos.sql');
        $param = [
            'codpes' => $codpes,
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Listar defesas em um intervalo de tempo
     *
     * Se não for informado o intervalo, mostrará o ano corrente
     *
     * @param Array $intervalo = ['inicio'=> '2020-01-01', 'fim' => '2021-01-01']
     * @return Array
     **/
    public static function listarDefesas($intervalo = [])
    {
        # Se não for passado o intervalo vamos listar as defesas do ano corrente
        if (empty($intervalo)) {
            $intervalo['inicio'] = Date('Y') . '-01-01';
            $intervalo['fim'] = Date('Y') . '-12-31';
        }

        $query = DB::getQuery('Posgraduacao.listarDefesas.sql');
        $query = str_replace('__unidades__', getenv('REPLICADO_CODUNDCLG'), $query);
        
        $param = [
            'inicio' => $intervalo['inicio'],
            'fim' => $intervalo['fim'],
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Obter todas defesas concluídas de uma pessoa
     * @param  int $codpes: Número USP do aluno.
     *
     * @return array
     **/
    public static function obterDefesas($codpes)
    {
        $query = DB::getQuery('Posgraduacao.obterDefesas.sql');
        $param = [
            'codpes' => $codpes,
        ];
        return DB::fetchAll($query, $param);
    }

    /**
     * Retorna nome e número USP dos alunos ativos nos programas de pós-graduação na unidade
     *
     * @author gabrielareisg em 11/02/2021
     * @param int $codare - código da área do programa de pós graduação
     * @param int $codundclg - código da unidade
     *
     * @return array
     */
    public static function listarAlunosAtivosPrograma($codare)
    {
        $query = " SELECT DISTINCT l.nompes, l.codpes FROM LOCALIZAPESSOA l
                    JOIN VINCULOPESSOAUSP v ON (l.codpes = v.codpes)
                    WHERE l.tipvin = 'ALUNOPOS'
                    AND l.codundclg = convert(int,:codundclg)
                    AND v.codare = convert(int,:codare)
                    AND l.sitatl = 'A'
                    ORDER BY v.nompes ASC ";
        $param = [
            'codare' => $codare,
            'codundclg' => getenv('REPLICADO_CODUNDCLG'),
        ];

        return DB::fetchAll($query, $param);
    }

    /**
     * Lista os programas de Pós-graduação da unidade
     *
     * Inicialmente em uso no uspdev/pessoas
     *
     * @return Array - lista contendo código e nome do programa
     * @author masakik, em 22/7/2021
     */
    public static function listarProgramas()
    {
        $query = DB::getQuery('Posgraduacao.listarProgramas.sql');
        $query = str_replace('__unidades__', getenv('REPLICADO_CODUNDCLG'), $query);

        return \Uspdev\Replicado\DB::fetchAll($query);
    }

    /**
     * Método para listar todos os dados das disciplinas de pós-graduação
     *
     * @return Array lista com com disciplinas
     * @author André Canale Garcia <acgarcia@sc.sp.br> (04/2022)
     */
    public static function listarDisciplinas()
    {
        $codclg = getenv('REPLICADO_CODUNDCLG');

        $query = "SELECT d.*
                  FROM 
                  (
                    SELECT MAX(numseqdis) AS numseqdis, sgldis
                    FROM dbo.DISCIPLINA
                    GROUP BY sgldis
                  ) AS tbl JOIN dbo.DISCIPLINA AS d ON d.sgldis = tbl.sgldis AND d.numseqdis = tbl.numseqdis
                  JOIN AREA ON AREA.codare = d.codare
                  JOIN CURSO ON CURSO.codcur = AREA.codcur
                  WHERE CURSO.codclg IN ({$codclg})
                    AND d.dtadtvdis IS NULL 
                  ORDER BY d.nomdis ASC";

        return DB::fetchAll($query);
    }
}
