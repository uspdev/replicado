<?php

namespace Uspdev\Replicado;

use Illuminate\Support\Arr;

class Lattes
{
    /**
     * Recebe o número USP e retorna o ID Lattes da pessoa.
     *
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function id($codpes)
    {
        $query = "SELECT idfpescpq from DIM_PESSOA_XMLUSP WHERE codpes = CONVERT(int,:codpes)";
        $param['codpes'] = $codpes;
        $result = DB::fetch($query, $param);
        return $result ? $result['idfpescpq'] : false;
    }

    /**
     * Recebe o ID Lattes e retorna o número USP da pessoa.
     *
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function retornarCodpesPorIDLattes($id)
    {
        $query = "SELECT codpes from DIM_PESSOA_XMLUSP WHERE idfpescpq  = CONVERT(varchar,:idfpescpq)";
        $param['idfpescpq'] = $id;
        $result = DB::fetch($query, $param);
        return $result ? $result['codpes'] : false;
    }

    /**
     * Recebe o número USP e retorna o binário zip do lattes
     *
     * @param Integer $codpes
     * @return String|Bool
     *
     * @author Masakik, ajustado para nova config do replicado em 2/2023 aprox
     */
    public static function obterZip($codpes)
    {
        # hotfix -  o utf8_encode estraga o zip
        Replicado::setConfig(['sybase' => false]);

        $query = "SELECT imgarqxml from DIM_PESSOA_XMLUSP WHERE codpes = CONVERT(int,:codpes)";
        $param['codpes'] = $codpes;
        $result = DB::fetch($query, $param);

        # hotfix -  o utf8_encode estraga o zip
        Replicado::setConfig(['reset' => true]);

        return $result ? $result['imgarqxml'] : false;
    }

    /**
     * Recebe o número USP e salva o zip do lattes
     *
     * @param Integer $codpes
     * @param $to (opt) Pasta onde o zip será salvo
     * @return Bool
     */
    public static function saveZip($codpes, $to = '/tmp')
    {
        $content = self::obterZip($codpes);
        if ($content) {
            $zipFile = fopen("{$to}/{$codpes}.zip", "w");
            fwrite($zipFile, $content);
            fclose($zipFile);
            return true;
        }
        return false;
    }

    /**
     * Recebe o número USP e salva o xml do lattes
     *
     * @param Integer $codpes
     * @param $to (opt) Pasta onde o xml será salvo
     * @return String|Bool
     */
    public static function verificarXml($codpes, $to = '/tmp')
    {
        $content = self::obterZip($codpes);
        if ($content) {
            $xml = Uteis::unzip($content);
            if ($xml) {
                $xmlFile = fopen("{$to}/{$codpes}.xml", "w");
                fwrite($xmlFile, $xml);
                fclose($xmlFile);
                return true;
            }
        }
        return false;
    }

    /**
     * Recebe o número USP e devolve XML do lattes
     *
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function obterXml($codpes)
    {
        $zip = self::obterZip($codpes);
        return $zip ? Uteis::unzip($zip) : false;
    }

    /**
     * Recebe o número USP e devolve json do lattes
     *
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function obterJson($codpes)
    {
        $xml = self::obterXml($codpes);
        return $xml ? json_encode(simplexml_load_string($xml)) : false;
    }

    /**
     * Recebe o número USP e devolve array do lattes
     *
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function obterArray($codpes)
    {
        $json = self::obterJson($codpes);
        return $json ? Uteis::utf8_converter(json_decode($json, true)) : false;
    }

    /**
     * Recebe o número USP e devolve array dos prêmios e títulos com o respectivo ano de prêmiação
     *
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function listarPremios($codpes, $lattes_array = null)
    {
        $lattes = $lattes_array ?? self::obterArray($codpes);
        if (!$lattes && !isset($lattes['DADOS-GERAIS'])) {
            return false;
        }

        $premios = $lattes['DADOS-GERAIS'];
        if (array_key_exists('PREMIOS-TITULOS', $premios)) {
            $premios = $lattes['DADOS-GERAIS']['PREMIOS-TITULOS']['PREMIO-TITULO'];
            $nome_premios = [];
            foreach ($premios as $p) {
                if (!isset($p['@attributes']['NOME-DO-PREMIO-OU-TITULO'])) {
                    return false;
                } else {
                    array_push($nome_premios, $p['@attributes']['NOME-DO-PREMIO-OU-TITULO'] . ' - Ano: ' . $p['@attributes']['ANO-DA-PREMIACAO']);
                }
            }
            return $nome_premios;
        } else {
            return false;
        }
    }

    /**
     * Recebe o número USP e devolve o resumo do currículo do lattes
     *
     * idioma: 'pt' -português (default) ou 'en' - inglês
     * lattes_array: (default null) curriculo lattes convertido em array. Nesse caso $codpes será ignorado.
     *
     * @param Integer $codpes
     * @param String $idioma (optional)
     * @param Array $lattes_array (optional)
     * @return String
     * @author Autor original, quando
     * @author Thiago Gomes Veríssimo, 1/4/2021, refatorado para usar Arr do illuminate
     */
    public static function retornarResumoCV($codpes, $idioma = 'pt', $lattes_array = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        $path = 'DADOS-GERAIS.RESUMO-CV.@attributes.TEXTO-RESUMO-CV-RH';
        if (strtolower($idioma) == 'en') {
            $path .= '-EN';
        }
        return html_entity_decode(Arr::get($lattes, $path, ''));
    }

    /**
     * (deprecado)Recebe o número USP e devolve a data da última atualização do currículo do lattes
     *
     * Em favor de retornarDataUltimaAtualizacao pois pega do SQL e não do curriculo lattes.
     * Também retorna formatado em dd/mm/yyyy
     *
     * @param Integer $codpes
     * @param Array $lattes_array (opt) Lattes convertido para array
     * @return Int|Bool
     * @deprecated por Masakik em 20/4/2023
     */
    public static function retornarUltimaAtualizacao($codpes, $lattes_array = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        return Arr::get($lattes, '@attributes.DATA-ATUALIZACAO', false);
    }

    /**
     * Recebe o número USP e devolve a data da última atualização do currículo do lattes
     *
     * Substituto de retornarUltimaAtualizacao
     *
     * @param Int $codpes
     * @return String formatado em dd/mm/yyyy
     * @author Masakik, em 20/4/3023
     */
    public static function retornarDataUltimaAtualizacao(int $codpes)
    {
        $query = "SELECT  CONVERT(VARCHAR(10), dtaultalt ,103) dtaultalt
            FROM DIM_PESSOA_XMLUSP
            WHERE codpes = CONVERT(int,:codpes)";

        $param['codpes'] = $codpes;
        $result = DB::fetch($query, $param);

        return $result ? $result['dtaultalt'] : false;
    }

    /**
     * Recebe um array com os autores no padrão do currícula lattes e retorna
     * o nome dos autores formatado com apenas o nome e ordem de autoria
     *
     * Auxiliar para listarArtigos(), listarLivrosPublicados(), etc
     *
     * @param Array $array
     * @return Array
     */
    protected static function listarAutores($array)
    {
        $aux_autores = [];
        if ($array) {

            foreach ($array as $autor) {
                array_push($aux_autores, [
                    "NOME-COMPLETO-DO-AUTOR" => Arr::get($autor, '@attributes', false) ? Arr::get($autor, '@attributes.NOME-COMPLETO-DO-AUTOR', false) : Arr::get($autor, 'NOME-COMPLETO-DO-AUTOR', false),
                    "NOME-PARA-CITACAO" => Arr::get($autor, '@attributes', false) ? Arr::get($autor, '@attributes.NOME-PARA-CITACAO', false) : Arr::get($autor, 'NOME-PARA-CITACAO', false),
                    "ORDEM-DE-AUTORIA" => Arr::get($autor, '@attributes', false) ? Arr::get($autor, '@attributes.ORDEM-DE-AUTORIA', false) : Arr::get($autor, 'ORDEM-DE-AUTORIA', false),
                ]);
            }
            usort($aux_autores, function ($a, $b) {
                return (int) $a['ORDEM-DE-AUTORIA'] - (int) $b['ORDEM-DE-AUTORIA'];
            });
            return $aux_autores;
        }
        return false;
    }

    /**
     * Verifica se os parâmetros se enquadram no filtro
     *
     * Serve para pegar uma lista de registros e filtrar por ano, periodo ou quantidade de registros
     * Auxiliar para listarArtigos(), etc, e todos os métodos que recebem $tipo como parâmetro
     *
     * se limit_ini for igual a -1, então retornará todos os registros
     * se $tipo for registros, irá retornar os últimos limit_ini registro;
     * se $tipo for anual, o limit vai pegar os registros dos limit_ini útimos anos;
     * se $tipo for período, irá pegar os registros do ano entre limit_ini e limit_fim.
     *
     * @param String $tipo Valores possíveis para determinar o filtro: 'anual' e 'registros', 'periodo'.
     * @param Integer $limit_ini Limite de retorno conforme o tipo.
     * @param Integer $limit_fim Se o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim
     * @param Integer $ano
     * @param Integer $i
     * @return Bool
     */
    protected static function verificarFiltro($tipo, $ano, $limit_ini, $limit_fim, $i)
    {
        if ($limit_ini == -1) {
            return true;
        }

        if ($tipo == 'registros') {
            if ($i > $limit_ini) {
                return false;
            }
        } else if ($tipo == 'anual') {
            if ( date('Y') - (int) $ano >= $limit_ini) {
                return false;
            }
        } else if ($tipo == 'periodo') {
            if ((int) $ano < $limit_ini || (int) $ano > $limit_fim) {
                return false;
            }
        }
        return true;
    }

    /**
     * Recebe o número USP e devolve array com os artigos mais recentes cadastrados no currículo Lattes
     *
     * TODO: refatorar problema 1o registro
     *
     * Campos retornados: título do artigo, nome da revista ou períodico, volume, número de páginas,
     * ano de publicação, ISSN e autores (json)
     *
     * Os campos $tipo, $limit_ini e $limit_fim são usado em diversos métodos e o signifcado e valores default são os mesmos
     * Default: tipo = registro, limit_ini = 5
     *
     * Dependendo de $tipo, o resultado é modificado:
     * $tipo == 'anual': retorna os artigos dos últimos $limit_ini anos
     * $tipo == 'registros': retorna os $limit_ini artigos mais recentes
     * $tipo == 'periodo': retorna todos os registros dos anos entre $limit_ini e $limit_fim
     *
     * @param Integer $codpes = Número USP
     * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros.
     * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos;
     * @param Integer $limit_fim = Se o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim
     * @return Array|Bool
     * @author modificado por Masakik, em 3/2023, issue #536
     */
    public static function listarArtigos($codpes, $lattes_array = [], $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        $lattes = $lattes_array ?? self::obterArray($codpes);
        if (!$lattes || !isset($lattes['PRODUCAO-BIBLIOGRAFICA'])) {
            return false;
        }

        $artigos = Arr::get($lattes, 'PRODUCAO-BIBLIOGRAFICA.ARTIGOS-PUBLICADOS.ARTIGO-PUBLICADO', false);
        if (!$artigos) {
            return false;
        }

        //ordena em ordem decrescente.
        usort($artigos, function ($a, $b) {
            if (!isset($b['DADOS-BASICOS-DO-ARTIGO']['@attributes']['ANO-DO-ARTIGO'])) {
                return 0;
            }
            return (int) $b['DADOS-BASICOS-DO-ARTIGO']['@attributes']['ANO-DO-ARTIGO'] - (int) $a['DADOS-BASICOS-DO-ARTIGO']['@attributes']['ANO-DO-ARTIGO'];
        });

        $i = 0;
        $ultimos_artigos = [];

        if ($artigos) {

            //verificação para saber se há apenas 1 artigo
            if (!isset($artigos[1]['@attributes']['SEQUENCIA-PRODUCAO'])) {

                $dados_basicos = (!isset($artigos['DADOS-BASICOS-DO-ARTIGO']) && isset($artigos[1])) ? 1 : 'DADOS-BASICOS-DO-ARTIGO';
                $detalhamento = (!isset($artigos['DETALHAMENTO-DO-ARTIGO']) && isset($artigos[2])) ? 2 : 'DETALHAMENTO-DO-ARTIGO';
                $autores = (!isset($artigos['AUTORES']) && isset($artigos[3])) ? 3 : 'AUTORES';

                $aux_autores = self::listarAutores(Arr::get($artigos, "{$autores}", []));

                $aux_artigo = [
                    'SEQUENCIA-PRODUCAO' => Arr::get($artigos, "@attributes.SEQUENCIA-PRODUCAO", 0),
                    'TITULO-DO-ARTIGO' => Arr::get($artigos, "{$dados_basicos}.@attributes.TITULO-DO-ARTIGO", ""),
                    'TITULO-DO-PERIODICO-OU-REVISTA' => Arr::get($artigos, "{$detalhamento}.@attributes.TITULO-DO-PERIODICO-OU-REVISTA", ""),
                    'VOLUME' => Arr::get($artigos, "{$detalhamento}.@attributes.VOLUME", ""),
                    'PAGINA-INICIAL' => Arr::get($artigos, "{$detalhamento}.@attributes.PAGINA-INICIAL", ""),
                    'PAGINA-FINAL' => Arr::get($artigos, "{$detalhamento}.@attributes.PAGINA-FINAL", ""),
                    'ANO' => Arr::get($artigos, "{$dados_basicos}.@attributes.ANO-DO-ARTIGO", ""),
                    'ISSN' => Arr::get($artigos, "{$detalhamento}.@attributes.ISSN", ""),
                    'AUTORES' => $aux_autores,
                ];

                if (!self::verificarFiltro($tipo, $aux_artigo['ANO'], $limit_ini, $limit_fim, $i)) {
                    return [];
                }

                array_push($ultimos_artigos, $aux_artigo);

            } else {

                foreach ($artigos as $val) {
                    $i++;

                    $dados_basicos = (!isset($val['DADOS-BASICOS-DO-ARTIGO']) && isset($val[1])) ? 1 : 'DADOS-BASICOS-DO-ARTIGO';
                    $detalhamento = (!isset($val['DETALHAMENTO-DO-ARTIGO']) && isset($val[2])) ? 2 : 'DETALHAMENTO-DO-ARTIGO';
                    $autores = (!isset($val['AUTORES']) && isset($val[3])) ? 3 : 'AUTORES';

                    $aux_autores = self::listarAutores(Arr::get($val, "{$autores}", []));

                    $aux_artigo = [
                        'SEQUENCIA-PRODUCAO' => Arr::get($val, "@attributes.SEQUENCIA-PRODUCAO", 0),
                        'TITULO-DO-ARTIGO' => Arr::get($val, "{$dados_basicos}.@attributes.TITULO-DO-ARTIGO", ""),
                        'TITULO-DO-PERIODICO-OU-REVISTA' => Arr::get($val, "{$detalhamento}.@attributes.TITULO-DO-PERIODICO-OU-REVISTA", ""),
                        'VOLUME' => Arr::get($val, "{$detalhamento}.@attributes.VOLUME", ""),
                        'PAGINA-INICIAL' => Arr::get($val, "{$detalhamento}.@attributes.PAGINA-INICIAL", ""),
                        'PAGINA-FINAL' => Arr::get($val, "{$detalhamento}.@attributes.PAGINA-FINAL", ""),
                        'ANO' => Arr::get($val, "{$dados_basicos}.@attributes.ANO-DO-ARTIGO", ""),
                        'ISSN' => Arr::get($val, "{$detalhamento}.@attributes.ISSN", ""),
                        'AUTORES' => $aux_autores,
                    ];

                    if (!self::verificarFiltro($tipo, $aux_artigo['ANO'], $limit_ini, $limit_fim, $i)) {
                        continue;
                    }

                    array_push($ultimos_artigos, $aux_artigo);

                }
            }

            return $ultimos_artigos;
        } else {
            return [];
        }

    }

    /**
     * Lista as linhas de pesquisa
     *
     * @param Integer $codpes
     * @param Array $lattes_array
     * @return String|Bool
     */
    public static function listarLinhasPesquisa($codpes, $lattes_array = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $linhas_de_pesquisa = [];
        $atuacao_profissional = Arr::get($lattes, 'DADOS-GERAIS.ATUACOES-PROFISSIONAIS.ATUACAO-PROFISSIONAL', false);
        if ($atuacao_profissional) {
            foreach ($atuacao_profissional as $ap) {

                if (Arr::get($ap, 'ATIVIDADES-DE-PESQUISA-E-DESENVOLVIMENTO.PESQUISA-E-DESENVOLVIMENTO', false)) {
                    foreach (Arr::get($ap, 'ATIVIDADES-DE-PESQUISA-E-DESENVOLVIMENTO.PESQUISA-E-DESENVOLVIMENTO', []) as $linha_pesquisa) {

                        foreach (Arr::get($linha_pesquisa, 'LINHA-DE-PESQUISA', $linha_pesquisa) as $lp) {
                            if (Arr::get($lp, '@attributes.TITULO-DA-LINHA-DE-PESQUISA', false)) {
                                array_push($linhas_de_pesquisa, Arr::get($lp, '@attributes.TITULO-DA-LINHA-DE-PESQUISA', ''));
                            }
                        }

                        if (Arr::get($linha_pesquisa, 'LINHA-DE-PESQUISA.@attributes.TITULO-DA-LINHA-DE-PESQUISA', false)) {
                            array_push($linhas_de_pesquisa, Arr::get($linha_pesquisa, 'LINHA-DE-PESQUISA.@attributes.TITULO-DA-LINHA-DE-PESQUISA', ''));
                        } elseif (Arr::get($linha_pesquisa, '@attributes.TITULO-DA-LINHA-DE-PESQUISA', false)) {
                            array_push($linhas_de_pesquisa, Arr::get($linha_pesquisa, '@attributes.TITULO-DA-LINHA-DE-PESQUISA', false));
                        }
                    }
                }
            }
            return $linhas_de_pesquisa;
        }
        return [];
    }

    /**
     * Recebe o número USP e devolve array com os livros publicados
     *
     * com o respectivo título do livro, ano, número de páginas, nome da editora e autores
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarLivrosPublicados($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        $livros = Arr::get($lattes, 'PRODUCAO-BIBLIOGRAFICA.LIVROS-E-CAPITULOS', false);
        if (!$livros) {
            return false;
        }

        if (array_key_exists('LIVROS-PUBLICADOS-OU-ORGANIZADOS', $livros)) {
            $livros = Arr::get($lattes, 'PRODUCAO-BIBLIOGRAFICA.LIVROS-E-CAPITULOS.LIVROS-PUBLICADOS-OU-ORGANIZADOS.LIVRO-PUBLICADO-OU-ORGANIZADO', []);
            if (!$livros) {
                return false;
            }

            $ultimos_livros = [];

            if (isset($livros['@attributes']['SEQUENCIA-PRODUCAO'])) {
                $dados_basicos = (!isset($livros['DADOS-BASICOS-DO-LIVRO']) && isset($livros[1])) ? 1 : 'DADOS-BASICOS-DO-LIVRO';
                $detalhamento = (!isset($livros['DETALHAMENTO-DO-LIVRO']) && isset($livros[2])) ? 2 : 'DETALHAMENTO-DO-LIVRO';
                $autores = (!isset($livros['AUTORES']) && isset($livros[3])) ? 3 : 'AUTORES';

                $aux_autores = self::listarAutores(Arr::get($livros, "{$autores}", []));

                $aux_livro = [
                    'TITULO-DO-LIVRO' => Arr::get($livros, "{$dados_basicos}.@attributes.TITULO-DO-LIVRO", ''),
                    'ANO' => Arr::get($livros, "{$dados_basicos}.@attributes.ANO", ''),
                    'NUMERO-DE-PAGINAS' => Arr::get($livros, "{$detalhamento}.@attributes.NUMERO-DE-PAGINAS", ''),
                    'NOME-DA-EDITORA' => Arr::get($livros, "{$detalhamento}.@attributes.NOME-DA-EDITORA", ''),
                    'CIDADE-DA-EDITORA' => Arr::get($livros, "{$detalhamento}.@attributes.CIDADE-DA-EDITORA", ''),
                    'ISBN' => Arr::get($livros, "{$detalhamento}.@attributes.ISBN", ''),
                    'AUTORES' => $aux_autores,
                ];

                if (!self::verificarFiltro($tipo, $aux_livro['ANO'], $limit_ini, $limit_fim, 1)) {
                    return false;
                }

                array_push($ultimos_livros, $aux_livro);
            } else {
                $i = 0;

                usort($livros, function ($a, $b) {
                    if (!isset($b['@attributes']['SEQUENCIA-PRODUCAO'])) {
                        return 0;
                    }
                    return (int) $b['@attributes']['SEQUENCIA-PRODUCAO'] - (int) $a['@attributes']['SEQUENCIA-PRODUCAO'];
                });
                foreach ($livros as $val) {
                    $i++;
                    $dados_basicos = (!isset($val['DADOS-BASICOS-DO-LIVRO']) && isset($val[1])) ? 1 : 'DADOS-BASICOS-DO-LIVRO';
                    $detalhamento = (!isset($val['DETALHAMENTO-DO-LIVRO']) && isset($val[2])) ? 2 : 'DETALHAMENTO-DO-LIVRO';
                    $autores = (!isset($val['AUTORES']) && isset($val[3])) ? 3 : 'AUTORES';

                    $aux_autores = self::listarAutores(Arr::get($val, "{$autores}", []));

                    $aux_livro = [
                        'TITULO-DO-LIVRO' => Arr::get($val, "{$dados_basicos}.@attributes.TITULO-DO-LIVRO", ''),
                        'ANO' => Arr::get($val, "{$dados_basicos}.@attributes.ANO", ''),
                        'NUMERO-DE-PAGINAS' => Arr::get($val, "{$detalhamento}.@attributes.NUMERO-DE-PAGINAS", ''),
                        'NOME-DA-EDITORA' => Arr::get($val, "{$detalhamento}.@attributes.NOME-DA-EDITORA", ''),
                        'CIDADE-DA-EDITORA' => Arr::get($val, "{$detalhamento}.@attributes.CIDADE-DA-EDITORA", ''),
                        'ISBN' => Arr::get($val, "{$detalhamento}.@attributes.ISBN", ''),
                        'AUTORES' => $aux_autores,
                    ];

                    if (!self::verificarFiltro($tipo, $aux_livro['ANO'], $limit_ini, $limit_fim, $i)) {
                        continue;
                    }

                    array_push($ultimos_livros, $aux_livro);
                }
            }

            return $ultimos_livros;
        } else {
            return false;
        }
    }

    /**
     * Recebe o número USP e devolve array com os textos publicados em revistas ou jornais
     *
     * @param Integer $codpes = Número USP
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarTextosJornaisRevistas($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['TEXTOS-EM-JORNAIS-OU-REVISTAS'])) {
            return false;
        }

        $textos_jornais_revistas = [];

        $aux_textos_jornais_revistas = Arr::get($lattes, 'PRODUCAO-BIBLIOGRAFICA.TEXTOS-EM-JORNAIS-OU-REVISTAS.TEXTO-EM-JORNAL-OU-REVISTA', false);
        if ($aux_textos_jornais_revistas) {
            $i = 0;
            if (isset($aux_textos_jornais_revistas['AUTORES'])) {
                $i++;
                $texto = $aux_textos_jornais_revistas;
                $autores = (!isset($texto['AUTORES']) && isset($texto[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($texto, "{$autores}", []));

                $aux_texto = [];
                $aux_texto['TITULO'] = Arr::get($texto, "DADOS-BASICOS-DO-TEXTO.@attributes.TITULO-DO-TEXTO", "");
                $aux_texto['TIPO'] = Arr::get($texto, "DADOS-BASICOS-DO-TEXTO.@attributes.NATUREZA", ""); //JORNAL OU REVISTA
                $aux_texto['SEQUENCIA-PRODUCAO'] = Arr::get($texto, "@attributes.SEQUENCIA-PRODUCAO", "");
                $aux_texto['ANO'] = Arr::get($texto, "DADOS-BASICOS-DO-TEXTO.@attributes.ANO-DO-TEXTO", "");
                $aux_texto['TITULO-DO-JORNAL-OU-REVISTA'] = Arr::get($texto, "DETALHAMENTO-DO-TEXTO.@attributes.TITULO-DO-JORNAL-OU-REVISTA", "");
                $aux_texto['DATA'] = Arr::get($texto, "DETALHAMENTO-DO-TEXTO.@attributes.DATA-DE-PUBLICACAO", "");
                $aux_texto['LOCAL-DE-PUBLICACAO'] = Arr::get($texto, "DETALHAMENTO-DO-TEXTO.@attributes.LOCAL-DE-PUBLICACAO", "");
                $aux_texto['VOLUME'] = Arr::get($texto, "DETALHAMENTO-DO-TEXTO.@attributes.VOLUME", "");
                $aux_texto['AUTORES'] = $aux_autores;

                if (!self::verificarFiltro($tipo, $aux_texto['ANO'], $limit_ini, $limit_fim, $i)) {
                    return false;
                }

                array_push($textos_jornais_revistas, $aux_texto);
            } else {
                foreach ($aux_textos_jornais_revistas as $texto) {
                    $i++;

                    $autores = (!isset($texto['AUTORES']) && isset($texto[3])) ? 3 : 'AUTORES';
                    $aux_autores = self::listarAutores(Arr::get($texto, "{$autores}", []));

                    $aux_texto = [];
                    $aux_texto['TITULO'] = Arr::get($texto, "DADOS-BASICOS-DO-TEXTO.@attributes.TITULO-DO-TEXTO", "");
                    $aux_texto['TIPO'] = Arr::get($texto, "DADOS-BASICOS-DO-TEXTO.@attributes.NATUREZA", ""); //JORNAL OU REVISTA
                    $aux_texto['SEQUENCIA-PRODUCAO'] = Arr::get($texto, "@attributes.SEQUENCIA-PRODUCAO", "");
                    $aux_texto['ANO'] = Arr::get($texto, "DADOS-BASICOS-DO-TEXTO.@attributes.ANO-DO-TEXTO", "");
                    $aux_texto['TITULO-DO-JORNAL-OU-REVISTA'] = Arr::get($texto, "DETALHAMENTO-DO-TEXTO.@attributes.TITULO-DO-JORNAL-OU-REVISTA", "");
                    $aux_texto['DATA'] = Arr::get($texto, "DETALHAMENTO-DO-TEXTO.@attributes.DATA-DE-PUBLICACAO", "");
                    $aux_texto['LOCAL-DE-PUBLICACAO'] = Arr::get($texto, "DETALHAMENTO-DO-TEXTO.@attributes.LOCAL-DE-PUBLICACAO", "");
                    $aux_texto['VOLUME'] = Arr::get($texto, "DETALHAMENTO-DO-TEXTO.@attributes.VOLUME", "");
                    $aux_texto['AUTORES'] = $aux_autores;

                    if (!self::verificarFiltro($tipo, $aux_texto['ANO'], $limit_ini, $limit_fim, $i)) {
                        continue;
                    }

                    array_push($textos_jornais_revistas, $aux_texto);
                }
            }
        } else {
            return false;
        }
        usort($textos_jornais_revistas, function ($a, $b) {
            if (!isset($b['SEQUENCIA-PRODUCAO'])) {
                return 0;
            }
            return (int) $b['SEQUENCIA-PRODUCAO'] - (int) $a['SEQUENCIA-PRODUCAO'];
        });

        return $textos_jornais_revistas;
    }

    /**
     * Recebe o número USP e devolve array com os trabalhos publicado em eventos/anais
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarTrabalhosAnais($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['TRABALHOS-EM-EVENTOS'])) {
            return false;
        }

        $trabalhos_anais = [];

        $aux_trabalhos_anais = Arr::get($lattes, 'PRODUCAO-BIBLIOGRAFICA.TRABALHOS-EM-EVENTOS.TRABALHO-EM-EVENTOS', false);

        if ($aux_trabalhos_anais) {
            $i = 0;
            if (isset($aux_trabalhos_anais['AUTORES'])) {
                $i++;
                $anais = $aux_trabalhos_anais;
                $autores = (!isset($anais['AUTORES']) && isset($anais[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($anais, "{$autores}", []));

                $aux_anais = [];
                $aux_anais['TITULO'] = Arr::get($anais, "DADOS-BASICOS-DO-TRABALHO.@attributes.TITULO-DO-TRABALHO", '');
                $aux_anais['TIPO'] = Arr::get($anais, "DADOS-BASICOS-DO-TRABALHO.@attributes.NATUREZA", '');
                $aux_anais['SEQUENCIA-PRODUCAO'] = Arr::get($anais, '@attributes.SEQUENCIA-PRODUCAO', '');
                $aux_anais['ANO'] = Arr::get($anais, "DADOS-BASICOS-DO-TRABALHO.@attributes.ANO-DO-TRABALHO", '');
                $aux_anais['NOME-DO-EVENTO'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.NOME-DO-EVENTO", '');
                $aux_anais['TITULO-DOS-ANAIS-OU-PROCEEDINGS'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.TITULO-DOS-ANAIS-OU-PROCEEDINGS", '');
                $aux_anais['CIDADE-DO-EVENTO'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.CIDADE-DO-EVENTO", '');
                $aux_anais['CIDADE-DA-EDITORA'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.CIDADE-DA-EDITORA", '');
                $aux_anais['NOME-DA-EDITORA'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.NOME-DA-EDITORA", '');
                $aux_anais['ANO-DE-REALIZACAO'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.ANO-DE-REALIZACAO", '');
                $aux_anais['PAGINA-INICIAL'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.PAGINA-INICIAL", '');
                $aux_anais['PAGINA-FINAL'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.PAGINA-FINAL", '');
                $aux_anais['AUTORES'] = $aux_autores;

                if (!self::verificarFiltro($tipo, $aux_anais['ANO'], $limit_ini, $limit_fim, $i)) {
                    return false;
                }
                array_push($trabalhos_anais, $aux_anais);
            } else {

                foreach ($aux_trabalhos_anais as $anais) {

                    $i++;

                    $autores = (!isset($anais['AUTORES']) && isset($anais[3])) ? 3 : 'AUTORES';
                    $aux_autores = self::listarAutores(Arr::get($anais, "{$autores}", []));

                    $aux_anais = [];
                    $aux_anais['TITULO'] = Arr::get($anais, "DADOS-BASICOS-DO-TRABALHO.@attributes.TITULO-DO-TRABALHO", '');
                    $aux_anais['TIPO'] = Arr::get($anais, "DADOS-BASICOS-DO-TRABALHO.@attributes.NATUREZA", '');
                    $aux_anais['SEQUENCIA-PRODUCAO'] = Arr::get($anais, '@attributes.SEQUENCIA-PRODUCAO', '');
                    $aux_anais['ANO'] = Arr::get($anais, "DADOS-BASICOS-DO-TRABALHO.@attributes.ANO-DO-TRABALHO", '');
                    $aux_anais['NOME-DO-EVENTO'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.NOME-DO-EVENTO", '');
                    $aux_anais['TITULO-DOS-ANAIS-OU-PROCEEDINGS'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.TITULO-DOS-ANAIS-OU-PROCEEDINGS", '');
                    $aux_anais['CIDADE-DO-EVENTO'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.CIDADE-DO-EVENTO", '');
                    $aux_anais['CIDADE-DA-EDITORA'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.CIDADE-DA-EDITORA", '');
                    $aux_anais['NOME-DA-EDITORA'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.NOME-DA-EDITORA", '');
                    $aux_anais['ANO-DE-REALIZACAO'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.ANO-DE-REALIZACAO", '');
                    $aux_anais['PAGINA-INICIAL'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.PAGINA-INICIAL", '');
                    $aux_anais['PAGINA-FINAL'] = Arr::get($anais, "DETALHAMENTO-DO-TRABALHO.@attributes.PAGINA-FINAL", '');
                    $aux_anais['AUTORES'] = $aux_autores;

                    if (!self::verificarFiltro($tipo, $aux_anais['ANO'], $limit_ini, $limit_fim, $i)) {
                        continue;
                    }
                    array_push($trabalhos_anais, $aux_anais);
                }
            }
        } else {
            return false;
        }
        usort($trabalhos_anais, function ($a, $b) {
            if (!isset($b['SEQUENCIA-PRODUCAO'])) {
                return 0;
            }
            return (int) $b['SEQUENCIA-PRODUCAO'] - (int) $a['SEQUENCIA-PRODUCAO'];
        });

        return $trabalhos_anais;

    }

    /**
     * Recebe o número USP e devolve array com os trabalhos técnicos
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarTrabalhosTecnicos($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-TECNICA']['TRABALHO-TECNICO'])) {
            return false;
        }

        $trabalhos_tecnicos = [];
        $i = 0;

        $trabalho = $lattes['PRODUCAO-TECNICA']['TRABALHO-TECNICO'];
        if (isset($trabalho['AUTORES'])) {
            $autores = (!isset($trabalho['AUTORES']) && isset($trabalho[3])) ? 3 : 'AUTORES';
            $aux_autores = self::listarAutores(Arr::get($trabalho, "{$autores}", []));

            $aux_trabalho_tec = [];
            $aux_trabalho_tec['TITULO'] = Arr::get($trabalho, "DADOS-BASICOS-DO-TRABALHO-TECNICO.@attributes.TITULO-DO-TRABALHO-TECNICO", "");
            $aux_trabalho_tec['TIPO'] = Arr::get($trabalho, "DADOS-BASICOS-DO-TRABALHO-TECNICO.@attributes.NATUREZA", "");
            $aux_trabalho_tec['SEQUENCIA-PRODUCAO'] = Arr::get($trabalho, "@attributes.SEQUENCIA-PRODUCAO", "");
            $aux_trabalho_tec['ANO'] = Arr::get($trabalho, "DADOS-BASICOS-DO-TRABALHO-TECNICO.@attributes.ANO", "");
            $aux_trabalho_tec['INSTITUICAO-FINANCIADORA'] = Arr::get($trabalho, "DETALHAMENTO-DO-TRABALHO-TECNICO.@attributes.INSTITUICAO-FINANCIADORA", "");
            $aux_trabalho_tec['AUTORES'] = $aux_autores;

            if (!self::verificarFiltro($tipo, $aux_trabalho_tec['ANO'], $limit_ini, $limit_fim, 1)) {
                return;
            }
            array_push($trabalhos_tecnicos, $aux_trabalho_tec);
        } else {
            foreach ($trabalho as $trabalho_tec) {
                $i++;

                $autores = (!isset($trabalho_tec['AUTORES']) && isset($trabalho_tec[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($trabalho_tec, "{$autores}", []));

                $aux_trabalho_tec = [];
                $aux_trabalho_tec['TITULO'] = Arr::get($trabalho_tec, "DADOS-BASICOS-DO-TRABALHO-TECNICO.@attributes.TITULO-DO-TRABALHO-TECNICO", "");
                $aux_trabalho_tec['TIPO'] = Arr::get($trabalho_tec, "DADOS-BASICOS-DO-TRABALHO-TECNICO.@attributes.NATUREZA", "");
                $aux_trabalho_tec['SEQUENCIA-PRODUCAO'] = Arr::get($trabalho_tec, "@attributes.SEQUENCIA-PRODUCAO", "");
                $aux_trabalho_tec['ANO'] = Arr::get($trabalho_tec, "DADOS-BASICOS-DO-TRABALHO-TECNICO.@attributes.ANO", "");
                $aux_trabalho_tec['INSTITUICAO-FINANCIADORA'] = Arr::get($trabalho_tec, "DETALHAMENTO-DO-TRABALHO-TECNICO.@attributes.INSTITUICAO-FINANCIADORA", "");
                $aux_trabalho_tec['AUTORES'] = $aux_autores;

                if (!self::verificarFiltro($tipo, $aux_trabalho_tec['ANO'], $limit_ini, $limit_fim, $i)) {
                    continue;
                }

                array_push($trabalhos_tecnicos, $aux_trabalho_tec);
            }
        }

        usort($trabalhos_tecnicos, function ($a, $b) {
            if (!isset($b['SEQUENCIA-PRODUCAO'])) {
                return 0;
            }
            return (int) $b['SEQUENCIA-PRODUCAO'] - (int) $a['SEQUENCIA-PRODUCAO'];
        });
        return $trabalhos_tecnicos;
    }

    /**
     * Recebe o número USP e devolve array com as apresentações de trabalhos técnicos
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarApresentacaoTrabalho($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['APRESENTACAO-DE-TRABALHO'])) {
            return false;
        }

        $apresentacao_trabalhos = [];

        $aux_apresentacao = Arr::get($lattes, 'PRODUCAO-TECNICA.DEMAIS-TIPOS-DE-PRODUCAO-TECNICA.APRESENTACAO-DE-TRABALHO', false);

        if ($aux_apresentacao) {
            $i = 0;
            if (isset($aux_apresentacao['@attributes'])) { //para só uma apresentação
                $autores = (!isset($aux_apresentacao['AUTORES']) && isset($aux_apresentacao[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($aux_apresentacao, "{$autores}", []));

                $aux_apresentacao_trabalho = [];
                $aux_apresentacao_trabalho['TITULO'] = Arr::get($aux_apresentacao, "DADOS-BASICOS-DA-APRESENTACAO-DE-TRABALHO.@attributes.TITULO", "");
                $aux_apresentacao_trabalho['TIPO'] = Arr::get($aux_apresentacao, "DADOS-BASICOS-DA-APRESENTACAO-DE-TRABALHO.@attributes.NATUREZA", "");
                $aux_apresentacao_trabalho['SEQUENCIA-PRODUCAO'] = Arr::get($aux_apresentacao, "@attributes.SEQUENCIA-PRODUCAO", "");
                $aux_apresentacao_trabalho['ANO'] = Arr::get($aux_apresentacao, "DADOS-BASICOS-DA-APRESENTACAO-DE-TRABALHO.@attributes.ANO", "");
                $aux_apresentacao_trabalho['AUTORES'] = $aux_autores;
                array_push($apresentacao_trabalhos, $aux_apresentacao_trabalho);
            } else {
                foreach ($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['APRESENTACAO-DE-TRABALHO'] as $apresentacao) {

                    $i++;
                    $autores = (!isset($apresentacao['AUTORES']) && isset($apresentacao[3])) ? 3 : 'AUTORES';
                    $aux_autores = self::listarAutores(Arr::get($apresentacao, "{$autores}", []));

                    $aux_apresentacao_trabalho = [];
                    $aux_apresentacao_trabalho['TITULO'] = Arr::get($apresentacao, "DADOS-BASICOS-DA-APRESENTACAO-DE-TRABALHO.@attributes.TITULO", "");
                    $aux_apresentacao_trabalho['TIPO'] = Arr::get($apresentacao, "DADOS-BASICOS-DA-APRESENTACAO-DE-TRABALHO.@attributes.NATUREZA", "");
                    $aux_apresentacao_trabalho['SEQUENCIA-PRODUCAO'] = Arr::get($apresentacao, "@attributes.SEQUENCIA-PRODUCAO", "");
                    $aux_apresentacao_trabalho['ANO'] = Arr::get($apresentacao, "DADOS-BASICOS-DA-APRESENTACAO-DE-TRABALHO.@attributes.ANO", "");
                    $aux_apresentacao_trabalho['AUTORES'] = $aux_autores;

                    if (!self::verificarFiltro($tipo, $aux_apresentacao_trabalho['ANO'], $limit_ini, $limit_fim, $i)) {
                        continue;
                    }

                    array_push($apresentacao_trabalhos, $aux_apresentacao_trabalho);
                }
            }
        }

        usort($apresentacao_trabalhos, function ($a, $b) {
            if (!isset($b['SEQUENCIA-PRODUCAO'])) {
                return 0;
            }
            return (int) $b['SEQUENCIA-PRODUCAO'] - (int) $a['SEQUENCIA-PRODUCAO'];
        });
        return ($apresentacao_trabalhos);
    }

    /**
     * Recebe o número USP e devolve array com organização de eventos
     *
     * indicadas em 'demais tipos de produção técnica'
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarOrganizacaoEvento($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        $eventos = [];

        $aux_eventos = Arr::get($lattes, 'PRODUCAO-TECNICA.DEMAIS-TIPOS-DE-PRODUCAO-TECNICA.ORGANIZACAO-DE-EVENTO', false);
        if ($aux_eventos) {
            $i = 0;
            if (isset($aux_eventos['AUTORES'])) {
                $i++;
                $evento = $aux_eventos;
                $autores = (!isset($evento['AUTORES']) && isset($evento[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($evento, "{$autores}", []));

                $aux_evento = [];
                $aux_evento['TITULO'] = Arr::get($evento, "DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO.@attributes.TITULO", "");
                $aux_evento['ANO'] = Arr::get($evento, "DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO.@attributes.ANO", "");
                $aux_evento['TIPO'] = Arr::get($evento, "DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO.@attributes.TIPO", "");
                $aux_evento['INSTITUICAO-PROMOTORA'] = Arr::get($evento, "DETALHAMENTO-DA-ORGANIZACAO-DE-EVENTO.@attributes.INSTITUICAO-PROMOTORA", "");
                $aux_evento['SEQUENCIA-PRODUCAO'] = Arr::get($evento, "@attributes.SEQUENCIA-PRODUCAO", "");
                $aux_evento['AUTORES'] = $aux_autores;

                if (!self::verificarFiltro($tipo, $aux_evento['ANO'], $limit_ini, $limit_fim, $i)) {
                    return;
                }

                array_push($eventos, $aux_evento);
            } else {
                foreach ($aux_eventos as $evento) {
                    $i++;
                    $autores = (!isset($evento['AUTORES']) && isset($evento[3])) ? 3 : 'AUTORES';
                    $aux_autores = self::listarAutores(Arr::get($evento, "{$autores}", []));

                    $aux_evento = [];
                    $aux_evento['TITULO'] = Arr::get($evento, "DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO.@attributes.TITULO", "");
                    $aux_evento['ANO'] = Arr::get($evento, "DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO.@attributes.ANO", "");
                    $aux_evento['TIPO'] = Arr::get($evento, "DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO.@attributes.TIPO", "");
                    $aux_evento['INSTITUICAO-PROMOTORA'] = Arr::get($evento, "DETALHAMENTO-DA-ORGANIZACAO-DE-EVENTO.@attributes.INSTITUICAO-PROMOTORA", "");
                    $aux_evento['SEQUENCIA-PRODUCAO'] = Arr::get($evento, "@attributes.SEQUENCIA-PRODUCAO", "");
                    $aux_evento['AUTORES'] = $aux_autores;

                    if (!self::verificarFiltro($tipo, $aux_evento['ANO'], $limit_ini, $limit_fim, $i)) {
                        continue;
                    }

                    array_push($eventos, $aux_evento);
                }
            }
        } else {
            return false;
        }

        return $eventos;
    }

    /**
     * Recebe o número USP e devolve array com as "outras" produções técnicas
     *
     * identificadas em 'Demais tipos de produção técnica'
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarOutrasProducoesTecnicas($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA'])) {
            return false;
        }

        $outras = [];

        $aux_outras = Arr::get($lattes, 'PRODUCAO-TECNICA.DEMAIS-TIPOS-DE-PRODUCAO-TECNICA.OUTRA-PRODUCAO-TECNICA', false);

        if (isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['OUTRA-PRODUCAO-TECNICA']['@attributes'])) { //se houver apenas um registro (sem foreach)
            $outro = $aux_outras;

            $autores = (!isset($outro['AUTORES']) && isset($outro[3])) ? 3 : 'AUTORES';
            $aux_autores = self::listarAutores(Arr::get($outro, "{$autores}", []));

            $aux_outros = [];
            $aux_outros['TITULO'] = Arr::get($outro, "DADOS-BASICOS-DE-OUTRA-PRODUCAO-TECNICA.@attributes.TITULO", "");
            $aux_outros['NATUREZA'] = Arr::get($outro, "DADOS-BASICOS-DE-OUTRA-PRODUCAO-TECNICA.@attributes.NATUREZA", "");
            $aux_outros['SEQUENCIA-PRODUCAO'] = Arr::get($outro, "@attributes.SEQUENCIA-PRODUCAO", "");
            $aux_outros['ANO'] = Arr::get($outro, "DADOS-BASICOS-DE-OUTRA-PRODUCAO-TECNICA.@attributes.ANO", "");
            $aux_outros['AUTORES'] = $aux_autores;

            if (!self::verificarFiltro($tipo, $aux_outros['ANO'], $limit_ini, $limit_fim, 1)) {
                return false;
            }

            array_push($outras, $aux_outros);

        } else if ($aux_outras) {
            $i = 0;
            foreach ($aux_outras as $outro) {
                $i++;
                $autores = (!isset($outro['AUTORES']) && isset($outro[3])) ? 3 : 'AUTORES';

                $aux_autores = self::listarAutores(Arr::get($outro, "{$autores}", []));

                $aux_outros = [];
                $aux_outros['TITULO'] = Arr::get($outro, "DADOS-BASICOS-DE-OUTRA-PRODUCAO-TECNICA.@attributes.TITULO", "");
                $aux_outros['NATUREZA'] = Arr::get($outro, "DADOS-BASICOS-DE-OUTRA-PRODUCAO-TECNICA.@attributes.NATUREZA", "");
                $aux_outros['SEQUENCIA-PRODUCAO'] = Arr::get($outro, "@attributes.SEQUENCIA-PRODUCAO", "");
                $aux_outros['ANO'] = Arr::get($outro, "DADOS-BASICOS-DE-OUTRA-PRODUCAO-TECNICA.@attributes.ANO", "");
                $aux_outros['AUTORES'] = $aux_autores;

                if (!self::verificarFiltro($tipo, $aux_outros['ANO'], $limit_ini, $limit_fim, $i)) {
                    continue;
                }

                array_push($outras, $aux_outros);
            }
        }

        return ($outras);
    }

    /**
     * Recebe o número USP e devolve array os cursos de curta duração
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarCursosCurtaDuracao($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA'])) {
            return false;
        }

        $cursos = [];

        $aux_cursos = Arr::get($lattes, 'PRODUCAO-TECNICA.DEMAIS-TIPOS-DE-PRODUCAO-TECNICA.CURSO-DE-CURTA-DURACAO-MINISTRADO', false);
        if ($aux_cursos) {
            $i = 0;

            if (isset($aux_cursos['@attributes'])) { //para quando só tiver um curso
                $autores = (!isset($aux_cursos['AUTORES']) && isset($aux_cursos[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($aux_cursos, "{$autores}", []));

                $aux_curso = [];
                $aux_curso['SEQUENCIA-PRODUCAO'] = Arr::get($aux_cursos, "@attributes.SEQUENCIA-PRODUCAO", "");
                $aux_curso['TITULO'] = Arr::get($aux_cursos, "DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO.@attributes.TITULO", "");
                $aux_curso['ANO'] = Arr::get($aux_cursos, "DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO.@attributes.ANO", "");
                $aux_curso['NIVEL-DO-CURSO'] = Arr::get($aux_cursos, "DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO.@attributes.NIVEL-DO-CURSO", "");
                $aux_curso['INSTITUICAO-PROMOTORA-DO-CURSO'] = Arr::get($aux_cursos, "DETALHAMENTO-DE-CURSOS-CURTA-DURACAO-MINISTRADO.@attributes.INSTITUICAO-PROMOTORA-DO-CURSO", "");
                $aux_curso['AUTORES'] = $aux_autores;
                array_push($cursos, $aux_curso);
            } else {
                foreach ($aux_cursos as $curso_curta_duracao) {

                    $i++;
                    $autores = (!isset($curso_curta_duracao['AUTORES']) && isset($curso_curta_duracao[3])) ? 3 : 'AUTORES';
                    $aux_autores = self::listarAutores(Arr::get($curso_curta_duracao, "{$autores}", []));

                    $aux_curso = [];
                    $aux_curso['SEQUENCIA-PRODUCAO'] = Arr::get($curso_curta_duracao, "@attributes.SEQUENCIA-PRODUCAO", "");
                    $aux_curso['TITULO'] = Arr::get($curso_curta_duracao, "DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO.@attributes.TITULO", "");
                    $aux_curso['ANO'] = Arr::get($curso_curta_duracao, "DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO.@attributes.ANO", "");
                    $aux_curso['NIVEL-DO-CURSO'] = Arr::get($curso_curta_duracao, "DADOS-BASICOS-DE-CURSOS-CURTA-DURACAO-MINISTRADO.@attributes.NIVEL-DO-CURSO", "");
                    $aux_curso['INSTITUICAO-PROMOTORA-DO-CURSO'] = Arr::get($curso_curta_duracao, "DETALHAMENTO-DE-CURSOS-CURTA-DURACAO-MINISTRADO.@attributes.INSTITUICAO-PROMOTORA-DO-CURSO", "");
                    $aux_curso['AUTORES'] = $aux_autores;

                    if (!self::verificarFiltro($tipo, $aux_curso['ANO'], $limit_ini, $limit_fim, $i)) {
                        continue;
                    }

                    array_push($cursos, $aux_curso);
                }
            }
        }
        return $cursos;
    }

    /**
     * Recebe o número USP e devolve array os relatórios de pesquisa
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarRelatorioPesquisa($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA'])) {
            return false;
        }

        $relatorios = [];

        $aux_relatorios = Arr::get($lattes, 'PRODUCAO-TECNICA.DEMAIS-TIPOS-DE-PRODUCAO-TECNICA.RELATORIO-DE-PESQUISA', false);
        if ($aux_relatorios) {
            if (isset($aux_relatorios['@attributes']['SEQUENCIA-PRODUCAO'])) {
                $autores = (!isset($aux_relatorios['AUTORES']) && isset($aux_relatorios[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($aux_relatorios, "{$autores}", []));

                $aux_relatorio = [];
                $aux_relatorio['SEQUENCIA-PRODUCAO'] = Arr::get($aux_relatorios, "@attributes.SEQUENCIA-PRODUCAO", "");
                $aux_relatorio['TITULO'] = Arr::get($aux_relatorios, "DADOS-BASICOS-DO-RELATORIO-DE-PESQUISA.@attributes.TITULO", "");
                $aux_relatorio['ANO'] = Arr::get($aux_relatorios, "DADOS-BASICOS-DO-RELATORIO-DE-PESQUISA.@attributes.ANO", "");
                $aux_relatorio['AUTORES'] = $aux_autores;

                if (!self::verificarFiltro($tipo, $aux_relatorio['ANO'], $limit_ini, $limit_fim, 1)) {
                    return false;
                }

                array_push($relatorios, $aux_relatorio);
            } else {

                $i = 0;
                foreach ($aux_relatorios as $relatorio) {

                    $i++;
                    $autores = (!isset($relatorio['AUTORES']) && isset($relatorio[3])) ? 3 : 'AUTORES';
                    $aux_autores = self::listarAutores(Arr::get($relatorio, "{$autores}", []));

                    $aux_relatorio = [];
                    $aux_relatorio['SEQUENCIA-PRODUCAO'] = Arr::get($relatorio, "@attributes.SEQUENCIA-PRODUCAO", "");
                    $aux_relatorio['TITULO'] = Arr::get($relatorio, "DADOS-BASICOS-DO-RELATORIO-DE-PESQUISA.@attributes.TITULO", "");
                    $aux_relatorio['ANO'] = Arr::get($relatorio, "DADOS-BASICOS-DO-RELATORIO-DE-PESQUISA.@attributes.ANO", "");
                    $aux_relatorio['AUTORES'] = $aux_autores;

                    if (!self::verificarFiltro($tipo, $aux_relatorio['ANO'], $limit_ini, $limit_fim, $i)) {
                        continue;
                    }

                    array_push($relatorios, $aux_relatorio);
                }
            }
        }

        return $relatorios;
    }

    /**
     * Recebe o número USP e devolve array com os materiais didáticos ou instrucionais do autor
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarMaterialDidaticoInstrucional($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA'])) {
            return false;
        }

        $materiais = [];

        if (isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['DESENVOLVIMENTO-DE-MATERIAL-DIDATICO-OU-INSTRUCIONAL']['@attributes']["SEQUENCIA-PRODUCAO"])) { //se houver apenas um registro (sem foreach)
            $material = $lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['DESENVOLVIMENTO-DE-MATERIAL-DIDATICO-OU-INSTRUCIONAL'];
            $autores = (!isset($material['AUTORES']) && isset($material[3])) ? 3 : 'AUTORES';
            $aux_autores = self::listarAutores(Arr::get($material, "{$autores}", []));

            $aux_material = [];
            $aux_material['SEQUENCIA-PRODUCAO'] = Arr::get($material, "@attributes.SEQUENCIA-PRODUCAO", "");
            $aux_material['TITULO'] = Arr::get($material, "DADOS-BASICOS-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL.@attributes.TITULO", "");
            $aux_material['ANO'] = Arr::get($material, "DADOS-BASICOS-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL.@attributes.ANO", "");
            $aux_material['NATUREZA'] = Arr::get($material, "DADOS-BASICOS-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL.@attributes.NATUREZA", "");
            $aux_material['AUTORES'] = $aux_autores;

            if (!self::verificarFiltro($tipo, $aux_material['ANO'], $limit_ini, $limit_fim, 1)) {
                return false;
            }

            array_push($materiais, $aux_material);

        } else
        if (isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['DESENVOLVIMENTO-DE-MATERIAL-DIDATICO-OU-INSTRUCIONAL'])) {
            $i = 0;
            foreach ($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['DESENVOLVIMENTO-DE-MATERIAL-DIDATICO-OU-INSTRUCIONAL'] as $material) {

                $i++;
                $autores = (!isset($material['AUTORES']) && isset($material[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($material, "{$autores}", []));

                $aux_material = [];
                $aux_material['SEQUENCIA-PRODUCAO'] = Arr::get($material, "@attributes.SEQUENCIA-PRODUCAO", "");
                $aux_material['TITULO'] = Arr::get($material, "DADOS-BASICOS-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL.@attributes.TITULO", "");
                $aux_material['ANO'] = Arr::get($material, "DADOS-BASICOS-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL.@attributes.ANO", "");
                $aux_material['NATUREZA'] = Arr::get($material, "DADOS-BASICOS-DO-MATERIAL-DIDATICO-OU-INSTRUCIONAL.@attributes.NATUREZA", "");
                $aux_material['AUTORES'] = $aux_autores;

                if (!self::verificarFiltro($tipo, $aux_material['ANO'], $limit_ini, $limit_fim, $i)) {
                    continue;
                }

                array_push($materiais, $aux_material);
            }
        }

        return $materiais;
    }

    /**
     * Recebe o número USP e devolve array com as "outras" produções bibliográficas, uma subcategoria das produções
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarOutrasProducoesBibliograficas($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'])) {
            return false;
        }

        $outras = [];

        $aux_outras_prod = Arr::get($lattes, 'PRODUCAO-BIBLIOGRAFICA.DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA.OUTRA-PRODUCAO-BIBLIOGRAFICA', false);
        if ($aux_outras_prod) {
            if (isset($aux_outras_prod['@attributes']['SEQUENCIA-PRODUCAO'])) {
                $autores = (!isset($aux_outras_prod['AUTORES']) && isset($aux_outras_prod[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($aux_outras_prod, "{$autores}", []));

                $aux_outros = [];
                $aux_outros['TITULO'] = Arr::get($aux_outras_prod, "DADOS-BASICOS-DE-OUTRA-PRODUCAO.@attributes.TITULO", "");
                $aux_outros['TIPO'] = Arr::get($aux_outras_prod, "DADOS-BASICOS-DE-OUTRA-PRODUCAO.@attributes.NATUREZA", "");
                $aux_outros['SEQUENCIA-PRODUCAO'] = Arr::get($aux_outras_prod, "@attributes.SEQUENCIA-PRODUCAO", "");
                $aux_outros['ANO'] = Arr::get($aux_outras_prod, "DADOS-BASICOS-DE-OUTRA-PRODUCAO.@attributes.ANO", "");
                $aux_outros['EDITORA'] = Arr::get($aux_outras_prod, "DETALHAMENTO-DE-OUTRA-PRODUCAO.@attributes.EDITORA", "");
                $aux_outros['CIDADE-DA-EDITORA'] = Arr::get($aux_outras_prod, "DETALHAMENTO-DE-OUTRA-PRODUCAO.@attributes.CIDADE-DA-EDITORA", "");
                $aux_outros['AUTORES'] = $aux_autores;

                if (self::verificarFiltro($tipo, $aux_outros['ANO'], $limit_ini, $limit_fim, 1)) {
                    array_push($outras, $aux_outros);
                }

            } else {
                $i = 0;
                foreach ($aux_outras_prod as $outro) {

                    $i++;
                    $autores = (!isset($outro['AUTORES']) && isset($outro[3])) ? 3 : 'AUTORES';
                    $aux_autores = self::listarAutores(Arr::get($outro, "{$autores}", []));

                    $aux_outros = [];
                    $aux_outros['TITULO'] = Arr::get($outro, "DADOS-BASICOS-DE-OUTRA-PRODUCAO.@attributes.TITULO", "");
                    $aux_outros['TIPO'] = Arr::get($outro, "DADOS-BASICOS-DE-OUTRA-PRODUCAO.@attributes.NATUREZA", "");
                    $aux_outros['SEQUENCIA-PRODUCAO'] = Arr::get($outro, "@attributes.SEQUENCIA-PRODUCAO", "");
                    $aux_outros['ANO'] = Arr::get($outro, "DADOS-BASICOS-DE-OUTRA-PRODUCAO.@attributes.ANO", "");
                    $aux_outros['EDITORA'] = Arr::get($outro, "DETALHAMENTO-DE-OUTRA-PRODUCAO.@attributes.EDITORA", "");
                    $aux_outros['CIDADE-DA-EDITORA'] = Arr::get($outro, "DETALHAMENTO-DE-OUTRA-PRODUCAO.@attributes.CIDADE-DA-EDITORA", "");
                    $aux_outros['AUTORES'] = $aux_autores;

                    if (!self::verificarFiltro($tipo, $aux_outros['ANO'], $limit_ini, $limit_fim, $i)) {
                        continue;
                    }

                    array_push($outras, $aux_outros);
                }
            }
        }

        $tipo_outras_bibliografias = [
            ['nome do caminho' => 'DA-TRADUCAO', 'nome' => 'TRADUCAO', 'nome extenso' => 'Tradução'],
            ['nome do caminho' => 'DO-PREFACIO-POSFACIO', 'nome' => 'PREFACIO-POSFACIO', 'nome extenso' => 'Prefácio, Pósfacio'],
            ['nome do caminho' => 'DA-PARTITURA', 'nome' => 'PARTITURA-MUSICAL', 'nome extenso' => 'Partitura Musical'],
        ];

        foreach ($tipo_outras_bibliografias as $tipo) {

            $aux = $lattes['PRODUCAO-BIBLIOGRAFICA']['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'];

            if (isset($aux[$tipo['nome']])) {

                $tipo_prod = $aux[$tipo['nome']];
                $i = 0;

                if (isset($tipo_prod['@attributes']['SEQUENCIA-PRODUCAO'])) { //quando só um tipo de produção
                    $autores = (!isset($tipo_prod['AUTORES']) && isset($tipo_prod[3])) ? 3 : 'AUTORES';
                    $aux_autores = self::listarAutores(Arr::get($tipo_prod, "{$autores}", []));

                    $aux_tipo = [];
                    $aux_tipo['TITULO'] = Arr::get($tipo_prod, "DADOS-BASICOS-" . $tipo['nome do caminho'] . ".@attributes.TITULO", "");
                    $aux_tipo['TIPO'] = isset($tipo_prod["DADOS-BASICOS-" . $tipo['nome do caminho'] . ""]["@attributes"]["TIPO"]) ? $tipo['nome extenso'] . '/' . ucfirst(strtolower($tipo_prod["DADOS-BASICOS-" . $tipo['nome do caminho'] . ""]["@attributes"]["TIPO"])) : $tipo['nome extenso'];
                    $aux_tipo['SEQUENCIA-PRODUCAO'] = Arr::get($tipo_prod, "@attributes.SEQUENCIA-PRODUCAO", "");
                    $aux_tipo['ANO'] = Arr::get($tipo_prod, "DADOS-BASICOS-" . $tipo['nome do caminho'] . ".@attributes.ANO", "");
                    $aux_tipo['CIDADE-DA-EDITORA'] = Arr::get($tipo_prod, "DETALHAMENTO-" . $tipo['nome do caminho'] . ".@attributes.CIDADE-DA-EDITORA", "");
                    $aux_tipo['EDITORA'] = Arr::get($tipo_prod, "DETALHAMENTO-" . $tipo['nome do caminho'] . ".@attributes.EDITORA-" . $tipo['nome do caminho'] . "", "");
                    $aux_tipo['AUTORES'] = $aux_autores;

                    if (self::verificarFiltro($tipo, $aux_tipo['ANO'], $limit_ini, $limit_fim, 1)) {
                        array_push($outras, $aux_tipo);
                    }

                } else {
                    foreach ($tipo_prod as $tp) {
                        $i++;

                        $autores = (!isset($tp['AUTORES']) && isset($tp[3])) ? 3 : 'AUTORES';
                        $aux_autores = self::listarAutores(Arr::get($tp, "{$autores}", []));

                        $aux_tipo = [];
                        $aux_tipo['TITULO'] = Arr::get($tp, "DADOS-BASICOS-" . $tipo['nome do caminho'] . ".@attributes.TITULO", "");
                        $aux_tipo['TIPO'] = isset($tp["DADOS-BASICOS-" . $tipo['nome do caminho'] . ""]["@attributes"]["TIPO"]) ? $tipo['nome extenso'] . '/' . ucfirst(strtolower($tp["DADOS-BASICOS-" . $tipo['nome do caminho'] . ""]["@attributes"]["TIPO"])) : $tipo['nome extenso'];
                        $aux_tipo['SEQUENCIA-PRODUCAO'] = Arr::get($tp, "@attributes.SEQUENCIA-PRODUCAO", "");
                        $aux_tipo['ANO'] = Arr::get($tp, "DADOS-BASICOS-" . $tipo['nome do caminho'] . ".@attributes.ANO", "");
                        $aux_tipo['CIDADE-DA-EDITORA'] = Arr::get($tp, "DETALHAMENTO-" . $tipo['nome do caminho'] . ".@attributes.CIDADE-DA-EDITORA", "");
                        $aux_tipo['EDITORA'] = Arr::get($tp, "DETALHAMENTO-" . $tipo['nome do caminho'] . ".@attributes.EDITORA-" . $tipo['nome do caminho'] . "", "");
                        $aux_tipo['AUTORES'] = $aux_autores;

                        if (!self::verificarFiltro($tipo, $aux_tipo['ANO'], $limit_ini, $limit_fim, $i)) {
                            continue;
                        }

                        array_push($outras, $aux_tipo);
                    }
                }

            }
        }
        usort($outras, function ($a, $b) {
            if (!isset($b['SEQUENCIA-PRODUCAO'])) {
                return 0;
            }
            return (int) $b['SEQUENCIA-PRODUCAO'] - (int) $a['SEQUENCIA-PRODUCAO'];
        });
        return $outras;
    }

    /**
     * Recebe o número USP e devolve array com capítulos de livros publicados
     *
     * com o respectivo título do capítulo, título do livro, número de volumes, página inicial e final do capítulo, ano e nome da editora.
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarCapitulosLivros($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS'])) {
            return false;
        }

        $capitulos = $lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS'];

        if (array_key_exists('CAPITULOS-DE-LIVROS-PUBLICADOS', $capitulos)) {
            $capitulos = Arr::get($lattes, 'PRODUCAO-BIBLIOGRAFICA.LIVROS-E-CAPITULOS.CAPITULOS-DE-LIVROS-PUBLICADOS.CAPITULO-DE-LIVRO-PUBLICADO', false);
            if (!($capitulos)) {
                return false;
            } else {
                //ordena em ordem decrescente.
                usort($capitulos, function ($a, $b) {
                    if (!isset($b['@attributes']['SEQUENCIA-PRODUCAO'])) {
                        return 0;
                    }
                    return (int) $b['@attributes']['SEQUENCIA-PRODUCAO'] - (int) $a['@attributes']['SEQUENCIA-PRODUCAO'];
                });
            }
            $i = 0;
            $ultimos_capitulos = [];
            if (isset($capitulos[1]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'])) { //quando tem apenas uma produção

                $autores = (!isset($capitulos['AUTORES']) && isset($capitulos[3])) ? 3 : 'AUTORES';
                $aux_autores = self::listarAutores(Arr::get($capitulos, "{$autores}", []));

                $aux_capitulo = [
                    'TITULO-DO-CAPITULO-DO-LIVRO' => $capitulos[1]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'] ?? '',
                    'TITULO-DO-LIVRO' => $capitulos[2]['@attributes']['TITULO-DO-LIVRO'] ?? '',
                    'ISBN' => $capitulos[2]['@attributes']['ISBN'] ?? '',
                    'NUMERO-DE-VOLUMES' => $capitulos[2]['@attributes']['NUMERO-DE-VOLUMES'] ?? '',
                    'PAGINA-INICIAL' => $capitulos[2]['@attributes']['PAGINA-INICIAL'] ?? '',
                    'PAGINA-FINAL' => $capitulos[2]['@attributes']['PAGINA-FINAL'] ?? '',
                    'ANO' => $capitulos[1]['@attributes']['ANO'] ?? '',
                    'NOME-DA-EDITORA' => $capitulos[2]['@attributes']['NOME-DA-EDITORA'] ?? '',
                    'CIDADE-DA-EDITORA' => $capitulos[2]['@attributes']['CIDADE-DA-EDITORA'] ?? '',
                    'AUTORES' => $aux_autores,
                ];

                if (!self::verificarFiltro($tipo, $aux_capitulo['ANO'], $limit_ini, $limit_fim, 1)) {
                    return false;
                }

                array_push($ultimos_capitulos, $aux_capitulo);
            } else {
                $i = 0;
                foreach ($capitulos as $val) {
                    $i++;
                    $dados_basicos = (!isset($val['DADOS-BASICOS-DO-CAPITULO']) && isset($val[1])) ? 1 : 'DADOS-BASICOS-DO-CAPITULO';
                    $detalhamento = (!isset($val['DETALHAMENTO-DO-CAPITULO']) && isset($val[2])) ? 2 : 'DETALHAMENTO-DO-CAPITULO';
                    $autores = (!isset($val['AUTORES']) && isset($val[3])) ? 3 : 'AUTORES';
                    $aux_autores = self::listarAutores(Arr::get($val, "{$autores}", []));

                    if (isset($val[$dados_basicos]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'])) {
                        $aux_capitulo = [
                            'TITULO-DO-CAPITULO-DO-LIVRO' => Arr::get($val, "{$dados_basicos}.@attributes.TITULO-DO-CAPITULO-DO-LIVRO", ""),
                            'TITULO-DO-LIVRO' => Arr::get($val, "{$detalhamento}.@attributes.TITULO-DO-LIVRO", ""),
                            'ISBN' => Arr::get($val, "{$detalhamento}.@attributes.ISBN", ""),
                            'NUMERO-DE-VOLUMES' => Arr::get($val, "{$detalhamento}.@attributes.NUMERO-DE-VOLUMES", ""),
                            'PAGINA-INICIAL' => Arr::get($val, "{$detalhamento}.@attributes.PAGINA-INICIAL", ""),
                            'PAGINA-FINAL' => Arr::get($val, "{$detalhamento}.@attributes.PAGINA-FINAL", ""),
                            'ANO' => Arr::get($val, "{$dados_basicos}.@attributes.ANO", ""),
                            'NOME-DA-EDITORA' => Arr::get($val, "{$detalhamento}.@attributes.NOME-DA-EDITORA", ""),
                            'CIDADE-DA-EDITORA' => Arr::get($val, "{$detalhamento}.@attributes.CIDADE-DA-EDITORA", ""),
                            'AUTORES' => $aux_autores,
                        ];

                        if (!self::verificarFiltro($tipo, $aux_capitulo['ANO'], $limit_ini, $limit_fim, $i)) {
                            continue;
                        }

                        array_push($ultimos_capitulos, $aux_capitulo);
                    }
                }
            }
            return $ultimos_capitulos;
        } else {
            return false;
        }
    }

    /**
     * Recebe o número USP e devolve lista contendo tese defendida pelo autor (MESTRADO ou DOUTORADO)
     *
     * Retorna o título da tese, palavras-chave e ano de obtenção
     * .
     * @param Integer $codpes
     * @param Array $lattes_array
     * @param String $tipo Tipo da tese: DOUTORADO ou MESTRADO, o valor default é DOUTORADO
     * @return Array|Bool
     */
    public static function listarTeses($codpes, $tipo = 'DOUTORADO', $lattes_array = null)
    {
        $lattes = $lattes_array ?? self::obterArray($codpes);

        if (!$lattes && !isset($lattes['DADOS-GERAIS'])) {
            return false;
        }

        $teses = $lattes['DADOS-GERAIS'];

        if (array_key_exists('FORMACAO-ACADEMICA-TITULACAO', $teses)) {

            if (!isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO'][$tipo])) {
                return false;
            }

            $teses = Arr::get($lattes, "DADOS-GERAIS.FORMACAO-ACADEMICA-TITULACAO.{$tipo}", []);
            $nome_teses = [];
            foreach ($teses as $p) {

                $palavras_chaves = '';
                for ($i = 1; $i <= 6; $i++) {
                    $key_i = 'PALAVRA-CHAVE-' . $i;
                    if (isset($teses['PALAVRAS-CHAVE']['@attributes'][$key_i])) {
                        $palavras_chaves .= $teses['PALAVRAS-CHAVE']['@attributes'][$key_i] . '; ';
                    } else if (isset($p['PALAVRAS-CHAVE']['@attributes'][$key_i])) {
                        $palavras_chaves .= $p['PALAVRAS-CHAVE']['@attributes'][$key_i] . '; ';
                    }
                }
                $palavras_chaves = str_replace(' ; ', '', $palavras_chaves);
                $palavras_chaves = str_replace(';;', '', $palavras_chaves);
                if (isset($p['@attributes']['TITULO-DA-DISSERTACAO-TESE'])) {
                    $titulo = $p['@attributes']['TITULO-DA-DISSERTACAO-TESE'];
                } else if (isset($p['TITULO-DA-DISSERTACAO-TESE'])) {
                    $titulo = $p['TITULO-DA-DISSERTACAO-TESE'];
                } else {
                    $titulo = '';
                }
                if (isset($p['@attributes']['ANO-DE-OBTENCAO-DO-TITULO'])) {
                    $ano = $p['@attributes']['ANO-DE-OBTENCAO-DO-TITULO'];
                } else if (isset($p['ANO-DE-OBTENCAO-DO-TITULO'])) {
                    $ano = $p['ANO-DE-OBTENCAO-DO-TITULO'];
                } else {
                    $ano = '';
                }
                if (strlen($titulo) > 0) {
                    array_push($nome_teses, ['TITULO' => $titulo, 'PALAVRAS-CHAVE' => $palavras_chaves, 'ANO-DE-OBTENCAO-DO-TITULO' => $ano]);
                }
            }
            return count($nome_teses) > 0 ? $nome_teses : false;

        } else {
            return false;
        }
    }

    /**
     * Recebe o número USP e retorna array com o título da tese de Livre-Docência
     *
     * @param Integer $codpes
     * @param Array $lattes_array
     * @return Array|Bool
     */
    public static function obterLivreDocencia($codpes, $lattes_array = null)
    {
        $lattes = $lattes_array ?? self::obterArray($codpes);

        if (!$lattes && !isset($lattes['DADOS-GERAIS'])) {
            return false;
        }

        $teses = $lattes['DADOS-GERAIS'];

        if (array_key_exists('FORMACAO-ACADEMICA-TITULACAO', $teses)) {

            if (!isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['LIVRE-DOCENCIA'])) {
                return false;
            }

            $teses = $lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['LIVRE-DOCENCIA'];
            $nome_teses = [];
            foreach ($teses as $p) {
                if (isset($p['@attributes']['TITULO-DO-TRABALHO'])) {
                    $titulo = $p['@attributes']['TITULO-DO-TRABALHO'];
                } else if (isset($p['TITULO-DO-TRABALHO'])) {
                    $titulo = $p['TITULO-DO-TRABALHO'];
                } else {
                    $titulo = '';
                }
                if (strlen($titulo) > 0) {
                    array_push($nome_teses, $titulo);
                }

            }
            return count($nome_teses) > 0 ? $nome_teses : false;

        } else {
            return false;
        }
    }

    /**
     * Recebe o número USP e retorna array com os título das teses de Mestrado onde o docente particiou como integrante da banca avaliadora.
     *
     * TODO: aqui deve ser listar
     *
     * @param Integer $codpes = Número USP
     * @param Array $lattes_array
     * @return Array|Bool
     */
    public static function retornarBancaMestrado($codpes, $lattes_array = null)
    {
        $lattes = $lattes_array ?? self::obterArray($codpes);

        if (!$lattes && !isset($lattes['DADOS-COMPLEMENTARES'])) {
            return false;
        }

        $bancas = $lattes['DADOS-COMPLEMENTARES'];

        if (array_key_exists('PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO', $bancas)) {
            if (!isset($lattes['DADOS-COMPLEMENTARES']['PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO']['PARTICIPACAO-EM-BANCA-DE-MESTRADO'])) {
                return false;
            }

            $bancas = $lattes['DADOS-COMPLEMENTARES']['PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO']['PARTICIPACAO-EM-BANCA-DE-MESTRADO'];
            $nome_bancas = [];
            foreach ($bancas as $b) {
                if (!isset($b['DADOS-BASICOS-DA-PARTICIPACAO-EM-BANCA-DE-MESTRADO']['@attributes']['TITULO'])) {
                    return false;
                } else {
                    array_push($nome_bancas, $b['DADOS-BASICOS-DA-PARTICIPACAO-EM-BANCA-DE-MESTRADO']['@attributes']['TITULO']);
                }

            }
            return $nome_bancas;
        } else {
            return false;
        }
    }

    /**
     * Recebe o número USP e retorna array com os títulos das teses de Doutorado onde o docente particiou como integrante da banca avaliadora.
     *
     * TODO: aqui deve ser listar
     *
     * @param Integer $codpes
     * @param Array $lattes_array
     * @return Array|Bool
     */
    public static function retornarBancaDoutorado($codpes, $lattes_array = null)
    {
        $lattes = $lattes_array ?? self::obterArray($codpes);

        if (!$lattes && !isset($lattes['DADOS-COMPLEMENTARES'])) {
            return false;
        }

        $bancas = $lattes['DADOS-COMPLEMENTARES'];

        if (array_key_exists('PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO', $bancas)) {
            if (!isset($lattes['DADOS-COMPLEMENTARES']['PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO']['PARTICIPACAO-EM-BANCA-DE-DOUTORADO'])) {
                return false;
            }

            $bancas = $lattes['DADOS-COMPLEMENTARES']['PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO']['PARTICIPACAO-EM-BANCA-DE-DOUTORADO'];
            $nome_bancas = [];
            foreach ($bancas as $b) {
                if (!isset($b['DADOS-BASICOS-DA-PARTICIPACAO-EM-BANCA-DE-DOUTORADO']['@attributes']['TITULO'])) {
                    return false;
                } else {
                    $aux = $b['DADOS-BASICOS-DA-PARTICIPACAO-EM-BANCA-DE-DOUTORADO']['@attributes']['TITULO'] ?? '';
                    if (isset($b['DETALHAMENTO-DA-PARTICIPACAO-EM-BANCA-DE-DOUTORADO']['@attributes']['NOME-DO-CANDIDATO'])) {
                        $aux .= "\n";
                        $aux .= $b['DETALHAMENTO-DA-PARTICIPACAO-EM-BANCA-DE-DOUTORADO']['@attributes']['NOME-DO-CANDIDATO'] ?? '';
                    }

                    array_push($nome_bancas, $aux);
                }
            }
            return $nome_bancas;
        } else {
            return false;
        }
    }

    /**
     * Recebe o número USP e retorna array com formação acadêmica
     *
     * Chave: titulo (GRADUACAO, MESTRADO, etc)
     * Valor: json contendo título do trabalho, nome da instituição, ano da formação acadêmica, etc
     *
     * TODO: Aqui deve ser listar
     *
     * @param Integer $codpes = Número USP
     * @param Array $lattes_array
     * @return Array|Bool
     */
    public static function retornarFormacaoAcademica($codpes, $lattes_array = null)
    {
        $lattes = $lattes_array ?? self::obterArray($codpes);

        if (!$lattes && !isset($lattes['DADOS-GERAIS'])) {
            return false;
        }

        $vinculos = $lattes['DADOS-GERAIS'];
        $formacao = [];

        if (array_key_exists('FORMACAO-ACADEMICA-TITULACAO', $vinculos)) {
            if (!isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO'])) {
                return false;
            }

            if (isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['GRADUACAO'])) {
                $aux = $lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['GRADUACAO'];
                $formacao['GRADUACAO'] = [];
                if (isset($aux['@attributes'])) { //só quando tiver uma graduação
                    $aux_grad = [];
                    $aux_grad['NOME-CURSO'] = Arr::get($aux, "@attributes.NOME-CURSO", "");
                    $aux_grad['TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO'] = Arr::get($aux, "@attributes.TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO", "");
                    $aux_grad['NOME-INSTITUICAO'] = Arr::get($aux, "@attributes.NOME-INSTITUICAO", "");
                    $aux_grad['ANO-DE-CONCLUSAO'] = Arr::get($aux, "@attributes.ANO-DE-CONCLUSAO", "");
                    $aux_grad['STATUS-DO-CURSO'] = Arr::get($aux, "@attributes.STATUS-DO-CURSO", "");
                    array_push($formacao['GRADUACAO'], $aux_grad);
                } else {
                    foreach ($aux as $graduacao) {
                        $aux_grad = [];
                        $aux_grad['NOME-CURSO'] = Arr::get($graduacao, "@attributes.NOME-CURSO", "");
                        $aux_grad['TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO'] = Arr::get($graduacao, "@attributes.TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO", "");
                        $aux_grad['NOME-INSTITUICAO'] = Arr::get($graduacao, "@attributes.NOME-INSTITUICAO", "");
                        $aux_grad['ANO-DE-CONCLUSAO'] = Arr::get($graduacao, "@attributes.ANO-DE-CONCLUSAO", "");
                        $aux_grad['STATUS-DO-CURSO'] = Arr::get($graduacao, "@attributes.STATUS-DO-CURSO", "");
                        array_push($formacao['GRADUACAO'], $aux_grad);
                    }
                }
                uasort($formacao['GRADUACAO'], function ($a, $b) {
                    return (int) $b['ANO-DE-CONCLUSAO'] - (int) $a['ANO-DE-CONCLUSAO'];
                });
            }

            if (isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['MESTRADO'])) {
                $aux = $lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['MESTRADO'];
                $formacao['MESTRADO'] = [];
                if (isset($aux['@attributes'])) { //só quando tiver um mestrado
                    $aux_mestrado = [];
                    $aux_mestrado['NOME-CURSO'] = Arr::get($aux, "@attributes.NOME-CURSO", "");
                    $aux_mestrado['TITULO-DA-DISSERTACAO-TESE'] = Arr::get($aux, "@attributes.TITULO-DA-DISSERTACAO-TESE", "");
                    $aux_mestrado['NOME-INSTITUICAO'] = Arr::get($aux, "@attributes.NOME-INSTITUICAO", "");
                    $aux_mestrado['ANO-DE-CONCLUSAO'] = Arr::get($aux, "@attributes.ANO-DE-CONCLUSAO", "");
                    $aux_mestrado['STATUS-DO-CURSO'] = Arr::get($aux, "@attributes.STATUS-DO-CURSO", "");
                    array_push($formacao['MESTRADO'], $aux_mestrado);
                } else {
                    foreach ($aux as $mestrado) {
                        $aux_mestrado = [];
                        $aux_mestrado['TITULO-DA-DISSERTACAO-TESE'] = Arr::get($mestrado, "@attributes.TITULO-DA-DISSERTACAO-TESE", "");
                        $aux_mestrado['NOME-INSTITUICAO'] = Arr::get($mestrado, "@attributes.NOME-INSTITUICAO", "");
                        $aux_mestrado['ANO-DE-CONCLUSAO'] = Arr::get($mestrado, "@attributes.ANO-DE-CONCLUSAO", "");
                        $aux_mestrado['STATUS-DO-CURSO'] = Arr::get($mestrado, "@attributes.STATUS-DO-CURSO", "");

                        array_push($formacao['MESTRADO'], $aux_mestrado);
                    }
                }
                uasort($formacao['MESTRADO'], function ($a, $b) {
                    return (int) $b['ANO-DE-CONCLUSAO'] - (int) $a['ANO-DE-CONCLUSAO'];
                });
            }

            if (isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['DOUTORADO'])) {
                $aux = $lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['DOUTORADO'];
                $formacao['DOUTORADO'] = [];
                if (isset($aux['@attributes'])) { //só quando tiver um doutorado
                    $aux_doutorado = [];
                    $aux_doutorado['NOME-CURSO'] = Arr::get($aux, "@attributes.NOME-CURSO", "");
                    $aux_doutorado['TITULO-DA-DISSERTACAO-TESE'] = Arr::get($aux, "@attributes.TITULO-DA-DISSERTACAO-TESE", "");
                    $aux_doutorado['NOME-INSTITUICAO'] = Arr::get($aux, "@attributes.NOME-INSTITUICAO", "");
                    $aux_doutorado['ANO-DE-CONCLUSAO'] = Arr::get($aux, "@attributes.ANO-DE-CONCLUSAO", "");
                    $aux_doutorado['STATUS-DO-CURSO'] = Arr::get($aux, "@attributes.STATUS-DO-CURSO", "");
                    array_push($formacao['DOUTORADO'], $aux_doutorado);
                } else {
                    foreach ($aux as $doutorado) {
                        $aux_doutorado = [];
                        $aux_doutorado['NOME-CURSO'] = Arr::get($doutorado, "@attributes.NOME-CURSO", "");
                        $aux_doutorado['TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO'] = Arr::get($doutorado, "@attributes.TITULO-DA-DISSERTACAO-TESE", "");
                        $aux_doutorado['NOME-INSTITUICAO'] = Arr::get($doutorado, "@attributes.NOME-INSTITUICAO", "");
                        $aux_doutorado['ANO-DE-CONCLUSAO'] = Arr::get($doutorado, "@attributes.ANO-DE-CONCLUSAO", "");
                        $aux_doutorado['STATUS-DO-CURSO'] = Arr::get($doutorado, "@attributes.STATUS-DO-CURSO", "");
                        array_push($formacao['DOUTORADO'], $aux_doutorado);
                    }
                }
                uasort($formacao['DOUTORADO'], function ($a, $b) {
                    return (int) $b['ANO-DE-CONCLUSAO'] - (int) $a['ANO-DE-CONCLUSAO'];
                });
            }

            if (isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['POS-DOUTORADO'])) {
                $aux = $lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['POS-DOUTORADO'];
                $formacao['POS-DOUTORADO'] = [];
                if (isset($aux['@attributes'])) { //só quando tiver um pós doutorado
                    $aux_posdoutorado = [];
                    $aux_posdoutorado['NOME-INSTITUICAO'] = Arr::get($aux, "@attributes.NOME-INSTITUICAO", "");
                    $aux_posdoutorado['ANO-DE-CONCLUSAO'] = Arr::get($aux, "@attributes.ANO-DE-CONCLUSAO", "");
                    $aux_posdoutorado['STATUS-DO-CURSO'] = Arr::get($aux, "@attributes.STATUS-DO-CURSO", "");
                    array_push($formacao['POS-DOUTORADO'], $aux_posdoutorado);
                } else {
                    foreach ($aux as $posdoutorado) {
                        $aux_posdoutorado = [];
                        $aux_posdoutorado['NOME-INSTITUICAO'] = Arr::get($posdoutorado, "@attributes.NOME-INSTITUICAO", "");
                        $aux_posdoutorado['ANO-DE-CONCLUSAO'] = Arr::get($posdoutorado, "@attributes.ANO-DE-CONCLUSAO", "");
                        $aux_posdoutorado['STATUS-DO-CURSO'] = Arr::get($posdoutorado, "@attributes.STATUS-DO-CURSO", "");
                        array_push($formacao['POS-DOUTORADO'], $aux_posdoutorado);
                    }
                }
                uasort($formacao['POS-DOUTORADO'], function ($a, $b) {
                    return (int) $b['ANO-DE-CONCLUSAO'] - (int) $a['ANO-DE-CONCLUSAO'];
                });
            }

            if (isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['ESPECIALIZACAO'])) {
                $aux = $lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['ESPECIALIZACAO'];
                $formacao['ESPECIALIZACAO'] = [];
                if (isset($aux['@attributes'])) {
                    $aux_especializacao = [];
                    $aux_especializacao['TITULO-DA-MONOGRAFIA'] = Arr::get($aux, "@attributes.TITULO-DA-MONOGRAFIA", "");
                    $aux_especializacao['NOME-INSTITUICAO'] = Arr::get($aux, "@attributes.NOME-INSTITUICAO", "");
                    $aux_especializacao['ANO-DE-CONCLUSAO'] = Arr::get($aux, "@attributes.ANO-DE-CONCLUSAO", "");
                    $aux_especializacao['STATUS-DO-CURSO'] = Arr::get($aux, "@attributes.STATUS-DO-CURSO", "");
                    array_push($formacao['ESPECIALIZACAO'], $aux_especializacao);
                } else {
                    foreach ($aux as $especializacao) {
                        $aux_especializacao = [];
                        $aux_especializacao['TITULO-DA-MONOGRAFIA'] = Arr::get($especializacao, "@attributes.TITULO-DA-MONOGRAFIA", "");
                        $aux_especializacao['NOME-INSTITUICAO'] = Arr::get($especializacao, "@attributes.NOME-INSTITUICAO", "");
                        $aux_especializacao['ANO-DE-CONCLUSAO'] = Arr::get($especializacao, "@attributes.ANO-DE-CONCLUSAO", "");
                        $aux_especializacao['STATUS-DO-CURSO'] = Arr::get($especializacao, "@attributes.STATUS-DO-CURSO", "");
                        array_push($formacao['ESPECIALIZACAO'], $aux_especializacao);
                    }
                }
                uasort($formacao['ESPECIALIZACAO'], function ($a, $b) {
                    return (int) $b['ANO-DE-CONCLUSAO'] - (int) $a['ANO-DE-CONCLUSAO'];
                });
            }

            if (isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['LIVRE-DOCENCIA'])) {
                $aux = $lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['LIVRE-DOCENCIA'];
                $formacao['LIVRE-DOCENCIA'] = [];
                if (isset($aux['@attributes'])) {
                    $aux_livredocencia = [];
                    $aux_livredocencia['TITULO-DO-TRABALHO'] = Arr::get($aux, "@attributes.TITULO-DO-TRABALHO", "");
                    $aux_livredocencia['NOME-INSTITUICAO'] = Arr::get($aux, "@attributes.NOME-INSTITUICAO", "");
                    $aux_livredocencia['ANO-DE-CONCLUSAO'] = Arr::get($aux, "@attributes.ANO-DE-OBTENCAO-DO-TITULO", "");
                    $aux_livredocencia['STATUS-DO-CURSO'] = Arr::get($aux, "@attributes.STATUS-DO-CURSO", "");
                    array_push($formacao['LIVRE-DOCENCIA'], $aux_livredocencia);
                } else {
                    foreach ($aux as $livredocencia) {
                        $aux_livredocencia = [];
                        $aux_livredocencia['TITULO-DO-TRABALHO'] = Arr::get($livredocencia, "@attributes.TITULO-DO-TRABALHO", "");
                        $aux_livredocencia['NOME-INSTITUICAO'] = Arr::get($livredocencia, "@attributes.NOME-INSTITUICAO", "");
                        $aux_livredocencia['ANO-DE-CONCLUSAO'] = Arr::get($livredocencia, "@attributes.ANO-DE-OBTENCAO-DO-TITULO", "");
                        $aux_livredocencia['STATUS-DO-CURSO'] = Arr::get($livredocencia, "@attributes.STATUS-DO-CURSO", "");
                        array_push($formacao['LIVRE-DOCENCIA'], $aux_livredocencia);
                    }
                }
                uasort($formacao['LIVRE-DOCENCIA'], function ($a, $b) {
                    return (int) $b['ANO-DE-CONCLUSAO'] - (int) $a['ANO-DE-CONCLUSAO'];
                });
            }
            return $formacao;
        }
    }

    /**
     * Recebe o número USP e retorna array com os vínculos profissionais atuais
     *
     * nome da instituição, ano de inicio e ano fim, tipo de vínculo e outras informações.
     *
     * Padrão tipo=periodo, de 2017 a 2020
     * TODO: ajustar esse padrão corretamente
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarFormacaoProfissional($codpes, $lattes_array = null, $tipo = 'periodo', $limit_ini = 2017, $limit_fim = 2020)
    {
        $lattes = $lattes_array ?? self::obterArray($codpes);

        if (!$lattes && !isset($lattes['DADOS-GERAIS'])) {
            return false;
        }

        $atuacoes = $lattes['DADOS-GERAIS'];

        if (array_key_exists('ATUACOES-PROFISSIONAIS', $atuacoes)) {

            if (!isset($lattes['DADOS-GERAIS']['ATUACOES-PROFISSIONAIS']['ATUACAO-PROFISSIONAL'])) {
                return false;
            }

            $atuacoes = $lattes['DADOS-GERAIS']['ATUACOES-PROFISSIONAIS']['ATUACAO-PROFISSIONAL'];
            $profissoes = [];

            if (isset($atuacoes['@attributes']['NOME-INSTITUICAO'])) {
                $aux = [];
                $aux['NOME-INSTITUICAO'] = $atuacoes['@attributes']['NOME-INSTITUICAO'];
                $aux['VINCULOS'] = [];
                if (isset($atuacoes['VINCULOS'])) {
                    foreach ($atuacoes['VINCULOS'] as $vinculo) {
                        $aux_vinculos = [];
                        if (isset($vinculo['@attributes']['ANO-INICIO'])) {
                            $aux_vinculos['ANO-INICIO'] = Arr::get($vinculo, "@attributes.ANO-INICIO", "");
                            $aux_vinculos['ANO-FIM'] = Arr::get($vinculo, "@attributes.ANO-FIM", "");
                            $aux_vinculos['TIPO-DE-VINCULO'] = Arr::get($vinculo, "@attributes.TIPO-DE-VINCULO", "");
                            $aux_vinculos['FLAG-VINCULO-EMPREGATICIO'] = Arr::get($vinculo, "@attributes.FLAG-VINCULO-EMPREGATICIO", "");
                            $aux_vinculos['OUTRAS-INFORMACOES'] = Arr::get($vinculo, "@attributes.OUTRAS-INFORMACOES", "");
                            $aux_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'] = Arr::get($vinculo, "@attributes.OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO", "");
                        } else if (isset($vinculo['ANO-INICIO'])) {
                            $aux_vinculos['ANO-INICIO'] = Arr::get($vinculo, "ANO-INICIO", "");
                            $aux_vinculos['ANO-FIM'] = Arr::get($vinculo, "ANO-FIM", "");
                            $aux_vinculos['TIPO-DE-VINCULO'] = Arr::get($vinculo, "TIPO-DE-VINCULO", "");
                            $aux_vinculos['FLAG-VINCULO-EMPREGATICIO'] = Arr::get($vinculo, "FLAG-VINCULO-EMPREGATICIO", "");
                            $aux_vinculos['OUTRAS-INFORMACOES'] = Arr::get($vinculo, "OUTRAS-INFORMACOES", "");
                            $aux_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'] = Arr::get($vinculo, "OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO", "");
                        }

                        if (!self::verificarFiltro($tipo, $aux_vinculos['ANO-INICIO'], $limit_ini, $limit_fim, 1)) {
                            continue;
                        }

                        array_push($aux['VINCULOS'], $aux_vinculos);
                    }
                }
                array_push($profissoes, $aux);
            } else {
                foreach ($atuacoes as $a) {
                    $aux = [];
                    $aux['NOME-INSTITUICAO'] = $a['@attributes']['NOME-INSTITUICAO'];
                    $aux['VINCULOS'] = [];

                    if (isset($a['VINCULOS']['@attributes'])) {
                        $aux['VINCULOS']['ANO-INICIO'] = Arr::get($a, "VINCULOS.@attributes.ANO-INICIO", "");
                        $aux['VINCULOS']['ANO-FIM'] = Arr::get($a, "VINCULOS.@attributes.ANO-FIM", "");
                        $aux['VINCULOS']['TIPO-DE-VINCULO'] = Arr::get($a, "VINCULOS.@attributes.TIPO-DE-VINCULO", "");
                        $aux['VINCULOS']['FLAG-VINCULO-EMPREGATICIO'] = Arr::get($a, "VINCULOS.@attributes.FLAG-VINCULO-EMPREGATICIO", "");
                        $aux['VINCULOS']['OUTRAS-INFORMACOES'] = Arr::get($a, "VINCULOS.@attributes.OUTRAS-INFORMACOES", "");
                        $aux['VINCULOS']['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'] = Arr::get($a, "VINCULOS.@attributes.OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO", "");
                    } else {
                        if (isset($a['VINCULOS'])) {
                            foreach ($a['VINCULOS'] as $vinculo) {
                                $aux_vinculos = [];
                                $aux_vinculos['ANO-INICIO'] = Arr::get($vinculo, "@attributes.ANO-INICIO", "");
                                $aux_vinculos['ANO-FIM'] = Arr::get($vinculo, "@attributes.ANO-FIM", "");
                                $aux_vinculos['TIPO-DE-VINCULO'] = Arr::get($vinculo, "@attributes.TIPO-DE-VINCULO", "");
                                $aux_vinculos['FLAG-VINCULO-EMPREGATICIO'] = Arr::get($vinculo, "@attributes.FLAG-VINCULO-EMPREGATICIO", "");
                                $aux_vinculos['OUTRAS-INFORMACOES'] = Arr::get($vinculo, "@attributes.OUTRAS-INFORMACOES", "");
                                $aux_vinculos['OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO'] = Arr::get($vinculo, "@attributes.OUTRO-ENQUADRAMENTO-FUNCIONAL-INFORMADO", "");

                                if (!self::verificarFiltro($tipo, $aux_vinculos['ANO-INICIO'], $limit_ini, $limit_fim, 1)) {
                                    continue;
                                }

                                array_push($aux['VINCULOS'], $aux_vinculos);
                            }
                        }
                    }
                    if (
                        (isset($aux['VINCULOS']) && $aux['VINCULOS'] != null && $aux['VINCULOS'] != "" && $aux['VINCULOS'] !== true && sizeof($aux['VINCULOS']) > 0)
                    ) {
                        array_push($profissoes, $aux);
                    }
                }
            }
            return $profissoes;
        } else {
            return false;
        }

    }

    /**
     * Recebe o número USP e retorna array com as participações em rádio ou TV
     *
     * o título da entrevista, emissora e nome para citação.
     *
     * Padrão tipo=periodo, de 2017 a 2020
     * TODO: ajustar esse padrão corretamente
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarRadioTV($codpes, $lattes_array = null, $tipo = 'periodo', $limit_ini = 2017, $limit_fim = 2020)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }

        if (!isset($lattes['PRODUCAO-TECNICA'])) {
            return false;
        }

        $producoes = $lattes['PRODUCAO-TECNICA'];

        if (array_key_exists('DEMAIS-TIPOS-DE-PRODUCAO-TECNICA', $producoes)) {

            if (!isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['PROGRAMA-DE-RADIO-OU-TV'])) {
                return false;
            }

            $producoes = $lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['PROGRAMA-DE-RADIO-OU-TV'];

            $nome_producoes = [];

            if (isset($producoes['@attributes']['SEQUENCIA-PRODUCAO'])) {
                $dados_basicos = (!isset($producoes['DADOS-BASICOS-DO-PROGRAMA-DE-RADIO-OU-TV']) && isset($producoes[1])) ? 1 : 'DADOS-BASICOS-DO-PROGRAMA-DE-RADIO-OU-TV';
                $detalhamento = (!isset($producoes['DETALHAMENTO-DO-PROGRAMA-DE-RADIO-OU-TV']) && isset($producoes[2])) ? 2 : 'DETALHAMENTO-DO-PROGRAMA-DE-RADIO-OU-TV';
                $autores = (!isset($producoes['AUTORES']) && isset($producoes[3])) ? 3 : 'AUTORES';

                $aux_autores = self::listarAutores(Arr::get($producoes, "{$autores}", []));

                $aux_producao = [
                    'TITULO' => Arr::get($producoes, "{$dados_basicos}.@attributes.TITULO", ""),
                    'ANO' => Arr::get($producoes, "{$dados_basicos}.@attributes.ANO", ""),
                    'EMISSORA' => Arr::get($producoes, "{$detalhamento}.@attributes.EMISSORA", ""),
                    'AUTORES' => $aux_autores,
                ];

                if (!self::verificarFiltro($tipo, $aux_producao['ANO'], $limit_ini, $limit_fim, 1)) {
                    return false;
                }

                array_push($nome_producoes, $aux_producao);

            } else {

                foreach ($producoes as $val) {

                    $dados_basicos = (!isset($val['DADOS-BASICOS-DO-PROGRAMA-DE-RADIO-OU-TV']) && isset($val[1])) ? 1 : 'DADOS-BASICOS-DO-PROGRAMA-DE-RADIO-OU-TV';
                    $detalhamento = (!isset($val['DETALHAMENTO-DO-PROGRAMA-DE-RADIO-OU-TV']) && isset($val[2])) ? 2 : 'DETALHAMENTO-DO-PROGRAMA-DE-RADIO-OU-TV';
                    $autores = (!isset($val['AUTORES']) && isset($val[3])) ? 3 : 'AUTORES';

                    $aux_autores = self::listarAutores(Arr::get($val, "{$autores}", []));

                    if (isset($val[$dados_basicos]['@attributes'])) {
                        $aux_producao = [
                            'TITULO' => Arr::get($val, "{$dados_basicos}.@attributes.TITULO", ""),
                            'ANO' => Arr::get($val, "{$dados_basicos}.@attributes.ANO", ""),
                            'EMISSORA' => Arr::get($val, "{$detalhamento}.@attributes.EMISSORA", ""),
                            'AUTORES' => $aux_autores,
                        ];
                        if (!self::verificarFiltro($tipo, $aux_producao['ANO'], $limit_ini, $limit_fim, 1)) {
                            continue;
                        }

                        array_push($nome_producoes, $aux_producao);

                    } else {
                        $aux_producao = [
                            'TITULO' => Arr::get($val, "{$dados_basicos}.TITULO", ""),
                            'ANO' => Arr::get($val, "{$dados_basicos}.ANO", ""),
                            'EMISSORA' => Arr::get($val, "{$detalhamento}.EMISSORA", ""),
                            'AUTORES' => $aux_autores,
                        ];
                        if (!self::verificarFiltro($tipo, $aux_producao['ANO'], $limit_ini, $limit_fim, 1)) {
                            continue;
                        }
                        array_push($nome_producoes, $aux_producao);
                    }
                }
            }
            return $nome_producoes;
        } else {
            return false;
        }

    }

    /**
     * Recebe o número USP e devolve OrcidID cadastrado no currículo lattes
     *
     * @param Integer $codpes
     * @param Array $lattes_array (opt) Currículo lattes, convertido para array
     * @return String|Bool
     */
    public static function retornarOrcidID($codpes, $lattes_array = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        return Arr::get($lattes, 'DADOS-GERAIS.@attributes.ORCID-ID', false);
    }

    /**
     * Recebe o número USP e retorna projetos de pesquisa
     *
     * TODO: está retornando fora de ordem de data, então pega os 5 cadastrados primeiro
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     */
    public static function listarProjetosPesquisa($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        $lattes = $lattes_array ?? self::obterArray($codpes);
        if (!$lattes && !isset($lattes['DADOS-GERAIS'])) {
            return false;
        }

        if (isset($lattes['DADOS-GERAIS']['ATUACOES-PROFISSIONAIS']['ATUACAO-PROFISSIONAL'])) {
            $atuacoes = $lattes['DADOS-GERAIS']['ATUACOES-PROFISSIONAIS']['ATUACAO-PROFISSIONAL'];

            $aux_pesquisas = [];

            foreach ($atuacoes as $pp) {
                if (isset($pp['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO']['PROJETO-DE-PESQUISA'])) {
                    $projeto = $pp['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO']['PROJETO-DE-PESQUISA'];
                    if (!isset($projeto['EQUIPE-DO-PROJETO'])) {
                        foreach ($projeto as $pesquisa) {
                            $integrantes = Arr::get($pesquisa, "EQUIPE-DO-PROJETO.INTEGRANTES-DO-PROJETO", []);
                            $aux_integrantes = self::listarAutores($integrantes);
                            $aux_projeto = [
                                'NOME-DO-PROJETO' => Arr::get($pesquisa, "@attributes.NOME-DO-PROJETO", ""),
                                'ANO-INICIO' => Arr::get($pesquisa, "@attributes.ANO-INICIO", ""),
                                'ANO-FIM' => Arr::get($pesquisa, "@attributes.ANO-FIM", ""),
                                'SITUACAO' => Arr::get($pesquisa, "@attributes.SITUACAO", ""),
                                'NATUREZA' => Arr::get($pesquisa, "@attributes.NATUREZA", ""),
                                'DESCRICAO-DO-PROJETO' => Arr::get($pesquisa, "@attributes.DESCRICAO-DO-PROJETO", ""),
                                'EQUIPE-DO-PROJETO' => $aux_integrantes,
                            ];

                            if (!self::verificarFiltro($tipo, $aux_projeto['ANO-INICIO'], $limit_ini, $limit_fim, 1)) {
                                continue;
                            }

                            array_push($aux_pesquisas, $aux_projeto);
                        }
                    } else {
                        $integrantes = Arr::get($projeto, "EQUIPE-DO-PROJETO.INTEGRANTES-DO-PROJETO", []);
                        $aux_integrantes = self::listarAutores($integrantes);
                        $aux_projeto = [
                            'NOME-DO-PROJETO' => Arr::get($projeto, "@attributes.NOME-DO-PROJETO", ""),
                            'ANO-INICIO' => Arr::get($projeto, "@attributes.ANO-INICIO", ""),
                            'ANO-FIM' => Arr::get($projeto, "@attributes.ANO-FIM", ""),
                            'SITUACAO' => Arr::get($projeto, "@attributes.SITUACAO", ""),
                            'NATUREZA' => Arr::get($projeto, "@attributes.NATUREZA", ""),
                            'DESCRICAO-DO-PROJETO' => Arr::get($projeto, "@attributes.DESCRICAO-DO-PROJETO", ""),
                            'EQUIPE-DO-PROJETO' => $aux_integrantes,
                        ];

                        if (!self::verificarFiltro($tipo, $aux_projeto['ANO-INICIO'], $limit_ini, $limit_fim, 1)) {
                            continue;
                        }

                        array_push($aux_pesquisas, $aux_projeto);
                    }

                } else if (isset($pp['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'])) {
                    $projetos_pesquisas = $pp['ATIVIDADES-DE-PARTICIPACAO-EM-PROJETO']['PARTICIPACAO-EM-PROJETO'];
                    $i = 0;
                    foreach ($projetos_pesquisas as $c) {
                        if (!isset($c['PROJETO-DE-PESQUISA'])) {
                            continue;
                        }

                        $dados_basicos = (!isset($c['PROJETO-DE-PESQUISA']) && isset($c[1])) ? 1 : 'PROJETO-DE-PESQUISA';
                        if (!isset($c['PROJETO-DE-PESQUISA']['EQUIPE-DO-PROJETO'])) {
                            foreach ($c['PROJETO-DE-PESQUISA'] as $pesquisa) {
                                $integrantes = Arr::get($pesquisa, "EQUIPE-DO-PROJETO.INTEGRANTES-DO-PROJETO", []);
                                $aux_integrantes = self::listarAutores($integrantes);
                                $aux_projeto = [
                                    'NOME-DO-PROJETO' => Arr::get($pesquisa, "@attributes.NOME-DO-PROJETO", ""),
                                    'ANO-INICIO' => Arr::get($pesquisa, "@attributes.ANO-INICIO", ""),
                                    'ANO-FIM' => Arr::get($pesquisa, "@attributes.ANO-FIM", ""),
                                    'SITUACAO' => Arr::get($pesquisa, "@attributes.SITUACAO", ""),
                                    'NATUREZA' => Arr::get($pesquisa, "@attributes.NATUREZA", ""),
                                    'DESCRICAO-DO-PROJETO' => Arr::get($pesquisa, "@attributes.DESCRICAO-DO-PROJETO", ""),
                                    'EQUIPE-DO-PROJETO' => $aux_integrantes,
                                ];

                                $i++;

                                if (!self::verificarFiltro($tipo, $aux_projeto['ANO-INICIO'], $limit_ini, $limit_fim, $i)) {
                                    continue;
                                }

                                array_push($aux_pesquisas, $aux_projeto);
                            }
                        } else {
                            if (isset($c['PROJETO-DE-PESQUISA']['EQUIPE-DO-PROJETO']['INTEGRANTES-DO-PROJETO'])) {
                                $integrantes = Arr::get($c, "PROJETO-DE-PESQUISA.EQUIPE-DO-PROJETO.INTEGRANTES-DO-PROJETO", []);
                                $aux_integrantes = self::listarAutores($integrantes);
                                $aux_projeto = [
                                    'NOME-DO-PROJETO' => Arr::get($c, "{$dados_basicos}.@attributes.NOME-DO-PROJETO", ""),
                                    'ANO-INICIO' => Arr::get($c, "{$dados_basicos}.@attributes.ANO-INICIO", ""),
                                    'ANO-FIM' => Arr::get($c, "{$dados_basicos}.@attributes.ANO-FIM", ""),
                                    'SITUACAO' => Arr::get($c, "{$dados_basicos}.@attributes.SITUACAO", ""),
                                    'NATUREZA' => Arr::get($c, "{$dados_basicos}.@attributes.NATUREZA", ""),
                                    'DESCRICAO-DO-PROJETO' => Arr::get($c, "{$dados_basicos}.@attributes.DESCRICAO-DO-PROJETO", ""),
                                    'EQUIPE-DO-PROJETO' => $aux_integrantes,
                                ];
                            } else {
                                $aux_projeto = [
                                    'NOME-DO-PROJETO' => Arr::get($c, "{$dados_basicos}.@attributes.NOME-DO-PROJETO", ""),
                                    'ANO-INICIO' => Arr::get($c, "{$dados_basicos}.@attributes.ANO-INICIO", ""),
                                    'ANO-FIM' => Arr::get($c, "{$dados_basicos}.@attributes.ANO-FIM", ""),
                                    'SITUACAO' => Arr::get($c, "{$dados_basicos}.@attributes.SITUACAO", ""),
                                    'NATUREZA' => Arr::get($c, "{$dados_basicos}.@attributes.NATUREZA", ""),
                                    'DESCRICAO-DO-PROJETO' => Arr::get($c, "{$dados_basicos}.@attributes.DESCRICAO-DO-PROJETO", ""),
                                ];
                            }

                            if (!self::verificarFiltro($tipo, $aux_projeto['ANO-INICIO'], $limit_ini, $limit_fim, $i)) {
                                continue;
                            }

                            $i++;
                            array_push($aux_pesquisas, $aux_projeto);
                        }
                    }
                }
            }
            return $aux_pesquisas;
        } else {
            return false;
        }
    }

    /**
     * Dada uma chave de busca, recupera os registros da $chave de $lattes_array
     *
     * Se informado $chaveOrdenacao, irá ordenar segundo $ordem
     * Default: sem ordenação, se ordenado, em ordem inversa
     *
     * @param Array $lattes_array
     * @param String $chave Chave de filtragem no formato dot - para Arr::get()
     * @param String $chaveOrdenacao Chave que será utiulizada no callback de ordenação
     * @param Int $ordem
     * @return Array
     * @author Masakik, em 20/4/2023
     */
    protected static function listarRegistrosPorChaveOrdenado($lattes_array, $chave, $chaveOrdenacao = null, $ordem = -1)
    {
        $registros = Arr::get($lattes_array, $chave, []);

        // trata registro unico
        $registros = isset($registros['@attributes']) ? [$registros] : $registros;

        // ordena pelo ano, ordem inversa, se especificado chave
        return $chaveOrdenacao ? self::ordenarRegistros($registros, $chaveOrdenacao, $ordem) : $registros;

    }

    /**
     * Auxiliar para ordernar registros de produção do lattes
     *
     * Usado nos métodos listarRegistrosPorChaveOrdenado(), listarOrientacoesConcluidasMestrado e similares
     *
     * @param Array $registros Registros a serem ordenados
     * @param String $chaveOrdenacao Chave do array no format dot para Arr::get()
     * @param Int $ordem Se -1 é decrescente, se 1, é crescente
     * @author Masakik, em 20/4/2023
     */
    protected static function ordenarRegistros($registros, $chaveOrdenacao, $ordem = -1)
    {
        usort($registros, function ($a, $b) use ($chaveOrdenacao) {
            if (Arr::get($b, $chaveOrdenacao, false) == false) {
                return 0;
            }
            if ($ordem = -1) {
                return (int) Arr::get($b, $chaveOrdenacao, false) - (int) Arr::get($a, $chaveOrdenacao, false);
            } else {
                return (int) Arr::get($a, $chaveOrdenacao, false) - (int) Arr::get($b, $chaveOrdenacao, false);
            }
        });
        return $registros;
    }

    /**
     * Lista as orientações concluídas de mestrado
     *
     * Traz os dados básicos, detalhamento, palavras-chave,
     * areas do conhecimento, setores de atividade e informações adicionais
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarOrientacoesConcluidasMestrado($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $registros = self::listarRegistrosPorChaveOrdenado(
            $lattes,
            'OUTRA-PRODUCAO.ORIENTACOES-CONCLUIDAS.ORIENTACOES-CONCLUIDAS-PARA-MESTRADO', //chave
            'DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO.@attributes.ANO' //chaveOrdenacao
        );
        $i = 0;
        $ret = [];
        foreach ($registros as $ent) {
            $i++;
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }
            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO']['@attributes'],
                $ent['DETALHAMENTO-DE-ORIENTACOES-CONCLUIDAS-PARA-MESTRADO']['@attributes'],
                $ent['PALAVRAS-CHAVE']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-1']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-2']['@attributes'] ?? [],
                $ent['SETORES-DE-ATIVIDADE']['@attributes'] ?? [],
                $ent['INFORMACOES-ADICIONAIS']['@attributes'] ?? [],
            );
        }
        return $ret;
    }

    /**
     * Lista as orientações concluídas de doutorado
     *
     * Traz os dados básicos, detalhamento, palavras-chave,
     * areas do conhecimento, setores de atividade e informações adicionais
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarOrientacoesConcluidasDoutorado($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $registros = self::listarRegistrosPorChaveOrdenado(
            $lattes,
            'OUTRA-PRODUCAO.ORIENTACOES-CONCLUIDAS.ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO',
            'DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO.@attributes.ANO',
        );
        $i = 0;
        $ret = [];
        foreach ($registros as $ent) {
            $i++;
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }
            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO']['@attributes'],
                $ent['DETALHAMENTO-DE-ORIENTACOES-CONCLUIDAS-PARA-DOUTORADO']['@attributes'],
                $ent['PALAVRAS-CHAVE']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-1']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-2']['@attributes'] ?? [],
                $ent['SETORES-DE-ATIVIDADE']['@attributes'] ?? [],
                $ent['INFORMACOES-ADICIONAIS']['@attributes'] ?? [],
            );
        }
        return $ret;
    }

    /**
     * Lista as orientações concluídas de pós doutorado
     *
     * Traz os dados básicos, detalhamento, palavras-chave,
     * areas do conhecimento, setores de atividade e informações adicionais
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarOrientacoesConcluidasPosDoutorado($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $registros = self::listarRegistrosPorChaveOrdenado(
            $lattes,
            'OUTRA-PRODUCAO.ORIENTACOES-CONCLUIDAS.ORIENTACOES-CONCLUIDAS-PARA-POS-DOUTORADO',
            'DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-POS-DOUTORADO.@attributes.ANO'
        );
        $i = 0;
        $ret = [];
        foreach ($registros as $ent) {
            $i++;
            $sai = [];
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-POS-DOUTORADO']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }
            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DE-ORIENTACOES-CONCLUIDAS-PARA-POS-DOUTORADO']['@attributes'],
                $ent['DETALHAMENTO-DE-ORIENTACOES-CONCLUIDAS-PARA-POS-DOUTORADO']['@attributes'],
                $ent['PALAVRAS-CHAVE']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-1']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-2']['@attributes'] ?? [],
                $ent['SETORES-DE-ATIVIDADE']['@attributes'] ?? [],
                $ent['INFORMACOES-ADICIONAIS']['@attributes'] ?? [],
            );
        }
        return $ret;
    }

    /**
     * Lista as orientações concluídas de TCC da graduação
     *
     * Traz os dados básicos, detalhamento
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarOrientacoesConcluidasTccGraduacao($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $chave = 'OUTRA-PRODUCAO.ORIENTACOES-CONCLUIDAS.OUTRAS-ORIENTACOES-CONCLUIDAS';
        $registros = self::listarRegistrosPorChaveOrdenado($lattes, $chave);

        // temos de filtrar diferente das outras produções
        $registros = Arr::where($registros, function ($value, $key) {
            return Arr::get($value, 'DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS.@attributes.NATUREZA') == 'TRABALHO_DE_CONCLUSAO_DE_CURSO_GRADUACAO' ? true : false;
        });

        $chaveOrdenacao = 'DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS.@attributes.ANO';
        $registros = self::ordenarRegistros($registros, $chaveOrdenacao);

        $i = 0;
        $ret = [];
        foreach ($registros as $ent) {
            $i++;
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }

            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS']['@attributes'],
                $ent['DETALHAMENTO-DE-OUTRAS-ORIENTACOES-CONCLUIDAS']['@attributes']
            );
        }
        return $ret;
    }

    /**
     * Lista as orientações concluídas de iniciação científica - IC
     *
     * Traz os dados básicos, detalhamento
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarOrientacoesConcluidasIC($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $chave = 'OUTRA-PRODUCAO.ORIENTACOES-CONCLUIDAS.OUTRAS-ORIENTACOES-CONCLUIDAS';
        $registros = self::listarRegistrosPorChaveOrdenado($lattes, $chave);

        // temos de filtrar diferente das outras produções
        $registros = Arr::where($registros, function ($value, $key) {
            return Arr::get($value, 'DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS.@attributes.NATUREZA') == 'INICIACAO_CIENTIFICA' ? true : false;
        });

        $chaveOrdenacao = 'DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS.@attributes.ANO';
        $registros = self::ordenarRegistros($registros, $chaveOrdenacao);

        $i = 0;
        $ret = [];
        foreach ($registros as $ent) {
            $i++;
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }
            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS']['@attributes'],
                $ent['DETALHAMENTO-DE-OUTRAS-ORIENTACOES-CONCLUIDAS']['@attributes']
            );
        }
        return $ret;
    }

    /**
     * Lista as monografias concluídas de cursos de aperfeicoamento e especialização
     *
     * Traz os dados básicos, detalhamento
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarMonografiasConcluidasAperfeicoamentoEspecializacao($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $chave = 'OUTRA-PRODUCAO.ORIENTACOES-CONCLUIDAS.OUTRAS-ORIENTACOES-CONCLUIDAS';
        $registros = self::listarRegistrosPorChaveOrdenado($lattes, $chave);

        // temos de filtrar diferente das outras produções
        $registros = Arr::where($registros, function ($value, $key) {
            return Arr::get($value, 'DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS.@attributes.NATUREZA') == 'MONOGRAFIA_DE_CONCLUSAO_DE_CURSO_APERFEICOAMENTO_E_ESPECIALIZACAO' ? true : false;
        });

        $chaveOrdenacao = 'DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS.@attributes.ANO';
        $registros = self::ordenarRegistros($registros, $chaveOrdenacao);

        $i = 0;
        $ret = [];
        foreach ($registros as $ent) {
            $i++;
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }

            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DE-OUTRAS-ORIENTACOES-CONCLUIDAS']['@attributes'],
                $ent['DETALHAMENTO-DE-OUTRAS-ORIENTACOES-CONCLUIDAS']['@attributes'],
                // será que tem outros dados complementares aqui ???
            );
        }
        return $ret;
    }

    /**
     * Lista as orientações em andamento de mestrado
     *
     * Traz os dados básicos, detalhamento, palavras-chave,
     * areas do conhecimento, setores de atividade e informações adicionais
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarOrientacoesEmAndamentoMestrado($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $registros = self::listarRegistrosPorChaveOrdenado(
            $lattes,
            'DADOS-COMPLEMENTARES.ORIENTACOES-EM-ANDAMENTO.ORIENTACAO-EM-ANDAMENTO-DE-MESTRADO', //chave
            'DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-MESTRADO.@attributes.ANO' //chaveOrdenacao
        );
        $i = 0;
        $ret = [];
        foreach ($registros as $ent) {
            $i++;
            $sai = [];
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-MESTRADO']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }
            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-MESTRADO']['@attributes'],
                $ent['DETALHAMENTO-DA-ORIENTACAO-EM-ANDAMENTO-DE-MESTRADO']['@attributes'],
                $ent['PALAVRAS-CHAVE']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-1']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-2']['@attributes'] ?? [],
                $ent['SETORES-DE-ATIVIDADE']['@attributes'] ?? [],
                $ent['INFORMACOES-ADICIONAIS']['@attributes'] ?? [],
            );
        }
        return $ret;
    }

    /**
     * Lista as orientações em andamento de doutorado
     *
     * Traz os dados básicos, detalhamento, palavras-chave,
     * areas do conhecimento, setores de atividade e informações adicionais
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarOrientacoesEmAndamentoDoutorado($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $registros = self::listarRegistrosPorChaveOrdenado(
            $lattes,
            'DADOS-COMPLEMENTARES.ORIENTACOES-EM-ANDAMENTO.ORIENTACAO-EM-ANDAMENTO-DE-DOUTORADO',
            'DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-DOUTORADO.@attributes.ANO'
        );
        $i = 0;
        $ret = [];
        foreach ($registros as $ent) {
            $i++;
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-DOUTORADO']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }
            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-DOUTORADO']['@attributes'],
                $ent['DETALHAMENTO-DA-ORIENTACAO-EM-ANDAMENTO-DE-DOUTORADO']['@attributes'],
                $ent['PALAVRAS-CHAVE']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-1']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-2']['@attributes'] ?? [],
                $ent['SETORES-DE-ATIVIDADE']['@attributes'] ?? [],
                $ent['INFORMACOES-ADICIONAIS']['@attributes'] ?? [],
            );
        }
        return $ret;
    }

    /**
     * Lista as orientações em andamento de pós doutorado
     *
     * Traz os dados básicos, detalhamento, palavras-chave,
     * areas do conhecimento, setores de atividade e informações adicionais
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarOrientacoesEmAndamentoPosDoutorado($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $chave = 'DADOS-COMPLEMENTARES.ORIENTACOES-EM-ANDAMENTO.ORIENTACAO-EM-ANDAMENTO-DE-POS-DOUTORADO';
        $chaveOrdenacao = 'DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-POS-DOUTORADO.@attributes.ANO';

        $i = 0;
        $ret = [];
        foreach (self::listarRegistrosPorChaveOrdenado($lattes, $chave, $chaveOrdenacao) as $ent) {
            $i++;
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-POS-DOUTORADO']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }
            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-POS-DOUTORADO']['@attributes'],
                $ent['DETALHAMENTO-DA-ORIENTACAO-EM-ANDAMENTO-DE-POS-DOUTORADO']['@attributes'],
                $ent['PALAVRAS-CHAVE']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-1']['@attributes'] ?? [],
                $ent['AREAS-DO-CONHECIMENTO']['AREA-DO-CONHECIMENTO-2']['@attributes'] ?? [],
                $ent['SETORES-DE-ATIVIDADE']['@attributes'] ?? [],
                $ent['INFORMACOES-ADICIONAIS']['@attributes'] ?? [],
            );
        }
        return $ret;
    }

    /**
     * Lista as orientações em andamento de iniciação científica - IC
     *
     * Traz os dados básicos, detalhamento
     *
     * @param Integer $codpes
     * @param String $tipo (ver método listarArtigos)
     * @param Integer $limit_ini (ver método listarArtigos)
     * @param Integer $limit_fim (ver método listarArtigos)
     * @return Array|Bool
     * @author Masakik, em 20/4/2023
     */
    public static function listarOrientacoesEmAndamentoIC($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null)
    {
        if (!$lattes = $lattes_array ?? self::obterArray($codpes)) {
            return false;
        }
        $chave = 'DADOS-COMPLEMENTARES.ORIENTACOES-EM-ANDAMENTO.ORIENTACAO-EM-ANDAMENTO-DE-INICIACAO-CIENTIFICA';
        $chaveOrdenacao = 'DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-INICIACAO-CIENTIFICA.@attributes.ANO';

        $i = 0;
        $ret = [];
        foreach (self::listarRegistrosPorChaveOrdenado($lattes, $chave, $chaveOrdenacao) as $ent) {
            $i++;
            $sai = [];
            if (!self::verificarFiltro($tipo, $ent['DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-INICIACAO-CIENTIFICA']['@attributes']['ANO'], $limit_ini, $limit_fim, $i)) {
                continue;
            }
            $ret[] = array_merge(
                $ent['DADOS-BASICOS-DA-ORIENTACAO-EM-ANDAMENTO-DE-INICIACAO-CIENTIFICA']['@attributes'],
                $ent['DETALHAMENTO-DA-ORIENTACAO-EM-ANDAMENTO-DE-INICIACAO-CIENTIFICA']['@attributes'],
            );
        }
        return $ret;
    }
}
