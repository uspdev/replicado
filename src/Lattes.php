<?php

namespace Uspdev\Replicado;

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
	    $query = "SELECT idfpescpq from DIM_PESSOA_XMLUSP WHERE codpes = convert(int,:codpes)";
		$param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetch($query, $param);
        if($result) return $result['idfpescpq'];
        return false;
    }
    
    /**
     * Recebe o número USP e retorna o binário zip do lattes
     * 
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function getZip($codpes){
        putenv('REPLICADO_SYBASE=0'); # hotfix -  o utf8_encode estraga o zip
        $query = "SELECT imgarqxml from DIM_PESSOA_XMLUSP WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetch($query, $param);

        if(!empty($result)) return $result['imgarqxml'];
        putenv('REPLICADO_SYBASE=1'); # hotfix -  o utf8_encode estraga o zip
        return false;
    }

    /**
     * Recebe o número USP e salva o zip do lattes
     * 
     * @param Integer $codpes
     * @return Bool
     */
    public static function saveZip($codpes, $to = '/tmp'){
        $content = self::getZip($codpes);
        if($content){
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
     * @return String|Bool
     */
    public static function saveXml($codpes, $to = '/tmp'){
        $content = self::getZip($codpes);
        if($content){
            $xml = Uteis::unzip($content);
            // Evitar salvar XML com 0 bytes
            if (!$xml) {
                return false;
            }
            $xmlFile = fopen("{$to}/{$codpes}.xml", "w");
            fwrite($xmlFile, $xml); 
            fclose($xmlFile);
            return true;
        }
        return false;
    }

    /**
     * Recebe o número USP e devolve XML do lattes
     * 
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function getXml($codpes){
        $zip = self::getZip($codpes);
        if(!$zip) return false;

        return Uteis::unzip($zip);
    }

    /**
     * Recebe o número USP e devolve json do lattes
     * 
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function getJson($codpes){
        $xml = self::getXml($codpes);
        if(!$xml) return false;

        return json_encode(simplexml_load_string($xml));
    }

    /**
     * Recebe o número USP e devolve array do lattes
     * 
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function getArray($codpes){
        $json = self::getJson($codpes);
        if(!$json) return false;
        return json_decode($json,TRUE);
    }

    /**
     * Recebe o número USP e devolve array dos prêmios e títulos cadastros no currículo Lattes,
     * com o respectivo ano de prêmiação
     * 
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function getPremios($codpes, $lattes_array = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes && !isset($lattes['DADOS-GERAIS'])) return false;

        $premios = $lattes['DADOS-GERAIS'];
        if(array_key_exists('PREMIOS-TITULOS',$premios)){
            $premios = $lattes['DADOS-GERAIS']['PREMIOS-TITULOS']['PREMIO-TITULO'];
            $nome_premios = [];
            foreach($premios as $p){
                if(!isset($p['@attributes']['NOME-DO-PREMIO-OU-TITULO'])){
                    return false;
                }else
                array_push($nome_premios, $p['@attributes']['NOME-DO-PREMIO-OU-TITULO'] . ' - Ano: ' . $p['@attributes']['ANO-DA-PREMIACAO']);
            }     
        return $nome_premios;
        }
        else return false;
     }
  
    /**
    * Recebe o número USP e devolve o resumo do currículo do lattes
    * 
    * @param Integer $codpes
    * @param String $idioma = Valores aceitos para idioma: 'pt' (português) e 'en' (inglês)
    * @return String|Bool
    * 
    */
    public static function getResumoCV($codpes, $idioma = 'pt', $lattes_array = null){
        $lattes = $lattes_array ?? self::getArray($codpes);

        if(!$lattes) return false;

        $campo = 'TEXTO-RESUMO-CV-RH';
        if(strtolower($idioma) == 'en') $campo .= '-EN'; 
        $resumo_cv = isset($lattes['DADOS-GERAIS']['RESUMO-CV']['@attributes'][$campo]) 
                    ? $lattes['DADOS-GERAIS']['RESUMO-CV']['@attributes'][$campo]
                    : false;
        
        return $resumo_cv;
    }

    /**
    * Recebe o número USP e devolve a última atualização do currículo do lattes
    * 
    * @param Integer $codpes
    * @return String|Bool
    * 
    */
    public static function getUltimaAtualizacao($codpes, $lattes_array = null){
        $lattes = $lattes_array ?? self::getArray($codpes);

        if(!$lattes) return false;
        
        return $lattes['@attributes']['DATA-ATUALIZACAO'];
    }

     /**
    * Recebe o número USP e devolve array com os últimos artigos cadastrados no currículo Lattes,
    * com o respectivo título do artigo, nome da revista ou períodico, volume, número de páginas e ano de publicação
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros. 
    * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos; se for registros, irá retornar os últimos n livros; se for período, irá pegar os registros do ano entre limit_ini e limit_fim. Se limit_ini for igaul a -1, então retornará todos os registros
    * @param Integer $limit_fim = Se  o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim 
    * @return String|Bool
    */

    public static function getArtigos($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes || !isset($lattes['PRODUCAO-BIBLIOGRAFICA'])) return false;
        $artigos = $lattes['PRODUCAO-BIBLIOGRAFICA'];


        if(array_key_exists('ARTIGOS-PUBLICADOS',$artigos)){
            $artigos = $lattes['PRODUCAO-BIBLIOGRAFICA']['ARTIGOS-PUBLICADOS']['ARTIGO-PUBLICADO'];
            //ordena em ordem decrescente.
            usort($artigos, function ($a, $b) {
                if(!isset($b['@attributes']['SEQUENCIA-PRODUCAO'])){
                    return 0;
                }
                return (int)$b['@attributes']['SEQUENCIA-PRODUCAO'] - (int)$a['@attributes']['SEQUENCIA-PRODUCAO'];
            });
            //verificação para saber se há apenas 1 artigo
            if(!isset($artigos[1]['@attributes']['SEQUENCIA-PRODUCAO'])){
                $aux = $artigos;
                $artigos = [];
                $artigos[0] = $aux;
            }         
            $i = 0;
            $ultimos_artigos = [];
            foreach($artigos as $val){
                $i++;

                $dados_basicos = (!isset($val['DADOS-BASICOS-DO-ARTIGO']) && isset($val[1])) ? 1 : 'DADOS-BASICOS-DO-ARTIGO';
                $detalhamento = (!isset($val['DETALHAMENTO-DO-ARTIGO']) && isset($val[2])) ? 2 : 'DETALHAMENTO-DO-ARTIGO';
                $autores = (!isset($val['AUTORES']) && isset($val[3])) ? 3 : 'AUTORES';
                
                $aux_autores = [];
                usort($val[$autores], function ($a, $b) {
                    if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                        return 0;
                    }
                    return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                });
               
                foreach($val[$autores] as $autor){
                    array_push($aux_autores, [
                        "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? '',
                        "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? '',
                        "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? '',
                        ]);
                }
                $aux_artigo = [
                    'TITULO-DO-ARTIGO' => $val[$dados_basicos]['@attributes']['TITULO-DO-ARTIGO'] ?? '',
                    'TITULO-DO-PERIODICO-OU-REVISTA' => $val[$detalhamento]['@attributes']['TITULO-DO-PERIODICO-OU-REVISTA'] ?? '',
                    'VOLUME' => $val[$detalhamento]['@attributes']['VOLUME'] ?? '',
                    'PAGINA-INICIAL' => $val[$detalhamento]['@attributes']['PAGINA-INICIAL'] ?? '',
                    'PAGINA-FINAL' => $val[$detalhamento]['@attributes']['PAGINA-FINAL'] ?? '',
                    'ANO' => $val[$dados_basicos]['@attributes']['ANO-DO-ARTIGO'] ?? '',
                    'ISSN' =>$val[$detalhamento]['@attributes']['ISSN'] ?? '',
                    'AUTORES' => $aux_autores
                ];
                /*
                if($tipo == 'registro'){
                    if($limit != -1 && $i > ($limit - 1) ) continue;  //-1 retorna tudo
                }else if($tipo == 'ano'){
                    if($limit != -1 &&  $aux_artigo['ANO'] <  $limit_ano ) continue;
                }
                */
                if($tipo == 'registros'){
                    if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
                }else if($tipo == 'anual'){
                    if($limit_ini != -1 &&  (int)$aux_artigo['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
                }else if($tipo == 'periodo'){
                    if($limit_ini != -1 && 
                        (
                        (int)$aux_artigo['ANO'] < $limit_ini ||
                        (int)$aux_artigo['ANO'] > $limit_fim 
                        )
                    ) continue; 
                }


                array_push($ultimos_artigos, $aux_artigo);
            }
            return $ultimos_artigos;
        } else return false;
    }

    /**
    * Recebe o número USP e devolve a linha de pesquisa
    * 
    * @param Integer $codpes
    * @return String|Bool
    * 
    */
    public static function getLinhasPesquisa($codpes, $lattes_array = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        $linhas_de_pesquisa = [];
        if(!$lattes) return false;

        if(isset($lattes['DADOS-GERAIS']['ATUACOES-PROFISSIONAIS']['ATUACAO-PROFISSIONAL']))
        {
            $atuacao_profissional = $lattes['DADOS-GERAIS']['ATUACOES-PROFISSIONAIS']['ATUACAO-PROFISSIONAL'];
            foreach($atuacao_profissional as $ap){
                
                if(isset($ap['ATIVIDADES-DE-PESQUISA-E-DESENVOLVIMENTO']['PESQUISA-E-DESENVOLVIMENTO'])){
                    foreach ($ap['ATIVIDADES-DE-PESQUISA-E-DESENVOLVIMENTO']['PESQUISA-E-DESENVOLVIMENTO'] as $linha_pesquisa) {
                        

                        if(isset($linha_pesquisa['LINHA-DE-PESQUISA'])){
                            foreach($linha_pesquisa['LINHA-DE-PESQUISA'] as $lp){
                                if(isset($lp['@attributes']['TITULO-DA-LINHA-DE-PESQUISA'])){
                                    array_push($linhas_de_pesquisa, $lp['@attributes']['TITULO-DA-LINHA-DE-PESQUISA']);
                                }
                            }
                        }else{
                            foreach($linha_pesquisa as $lp){
                                if(isset($lp['@attributes']['TITULO-DA-LINHA-DE-PESQUISA'])){
                                    array_push($linhas_de_pesquisa, $lp['@attributes']['TITULO-DA-LINHA-DE-PESQUISA']);
                                }
                            }
                        }
                        
                        if(isset($linha_pesquisa['LINHA-DE-PESQUISA']['@attributes']['TITULO-DA-LINHA-DE-PESQUISA'])){
                            array_push($linhas_de_pesquisa, $linha_pesquisa['LINHA-DE-PESQUISA']['@attributes']['TITULO-DA-LINHA-DE-PESQUISA']);
                        }elseif (isset($linha_pesquisa['@attributes']['TITULO-DA-LINHA-DE-PESQUISA'])) {
                            array_push($linhas_de_pesquisa, $linha_pesquisa['@attributes']['TITULO-DA-LINHA-DE-PESQUISA']);
                        }
                    }
                }
            }
            return $linhas_de_pesquisa;
        }
        return false;
    
    }

    /**
    * Recebe o número USP e devolve array com os livros publicados cadastrados no currículo Lattes,
    * com o respectivo título do livro, ano, número de páginas, nome da editora e autores
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros. 
    * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos; se for registros, irá retornar os últimos n livros; se for período, irá pegar os registros do ano entre limit_ini e limit_fim. Se limit_ini for igaul a -1, então retornará todos os registros
    * @param Integer $limit_fim = Se  o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim 
    * @return String|Bool
    */
    public static function getLivrosPublicados($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS'])) return false;
        

        $livros = $lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS'];
        if(array_key_exists('LIVROS-PUBLICADOS-OU-ORGANIZADOS',$livros)){
            $livros = $lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS']['LIVROS-PUBLICADOS-OU-ORGANIZADOS']['LIVRO-PUBLICADO-OU-ORGANIZADO'];
                if(!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS']['LIVROS-PUBLICADOS-OU-ORGANIZADOS']['LIVRO-PUBLICADO-OU-ORGANIZADO'])){
                    return false;
                }else
            $i = 0;
            $ultimos_livros = [];
            
            usort($livros, function ($a, $b) {
                if(!isset($b['@attributes']['SEQUENCIA-PRODUCAO'])){
                    return 0;
                }
                return (int)$b['@attributes']['SEQUENCIA-PRODUCAO'] - (int)$a['@attributes']['SEQUENCIA-PRODUCAO'];
            });
            foreach($livros as $val){
                $i++;
                $dados_basicos = (!isset($val['DADOS-BASICOS-DO-LIVRO']) && isset($val[1])) ? 1 : 'DADOS-BASICOS-DO-LIVRO';
                $detalhamento = (!isset($val['DETALHAMENTO-DO-LIVRO']) && isset($val[2])) ? 2 : 'DETALHAMENTO-DO-LIVRO';
                $autores = (!isset($val['AUTORES']) && isset($val[3])) ? 3 : 'AUTORES';
                
                $aux_autores = [];
                if(isset($val[$autores])){

                    usort($val[$autores], function ($a, $b) {
                        if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                            return 0;
                        }
                        return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                    });
                    
                    foreach($val[$autores] as $autor){
                        array_push($aux_autores, [
                            "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? '',
                            "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? '',
                            "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? '',
                            ]);
                    }
                }

                $aux_livro = [
                    'TITULO-DO-LIVRO' => $val[$dados_basicos]['@attributes']['TITULO-DO-LIVRO'] ?? '',
                    'ANO' => $val[$dados_basicos]['@attributes']['ANO'] ?? '',
                    'NUMERO-DE-PAGINAS' => $val[$detalhamento]['@attributes']['NUMERO-DE-PAGINAS'] ?? '',
                    'NOME-DA-EDITORA' => $val[$detalhamento]['@attributes']['NOME-DA-EDITORA'] ?? '',
                    'CIDADE-DA-EDITORA' => $val[$detalhamento]['@attributes']['CIDADE-DA-EDITORA'] ?? '',
                    'ISBN' => $val[$detalhamento]['@attributes']['ISBN'] ?? '',
                    'AUTORES' => $aux_autores
                ];
                
                
                if($tipo == 'registros'){
                    if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
                }else if($tipo == 'anual'){
                    if($limit_ini != -1 &&  (int)$aux_livro['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
                }else if($tipo == 'periodo'){
                    if($limit_ini != -1 && 
                        (
                        (int)$aux_livro['ANO'] < $limit_ini ||
                        (int)$aux_livro['ANO'] > $limit_fim 
                        )
                    ) continue; 
                }
                
                array_push($ultimos_livros, $aux_livro);
            }
            
            
            return $ultimos_livros;
        } else return false;
    }


    /**
    * Recebe o número USP e devolve array com os textos em revistas ou jornais publicados cadastrados no currículo Lattes
    * 
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros. 
    * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos; se for registros, irá retornar os últimos n livros; se for período, irá pegar os registros do ano entre limit_ini e limit_fim. Se limit_ini for igaul a -1, então retornará todos os registros
    * @param Integer $limit_fim = Se  o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim 
    * @return String|Bool
    */
    public static function getTextosJornaisRevistas($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['TEXTOS-EM-JORNAIS-OU-REVISTAS'])) return false;
        $textos_jornais_revistas = [];

        if(isset($lattes['PRODUCAO-BIBLIOGRAFICA']['TEXTOS-EM-JORNAIS-OU-REVISTAS']['TEXTO-EM-JORNAL-OU-REVISTA'])){
            $i = 0;
            foreach($lattes['PRODUCAO-BIBLIOGRAFICA']['TEXTOS-EM-JORNAIS-OU-REVISTAS']['TEXTO-EM-JORNAL-OU-REVISTA'] as $texto){
                $i++;
                $autores = (!isset($texto['AUTORES']) && isset($texto[3])) ? 3 : 'AUTORES';
                
                $aux_autores = [];
                if(isset($texto[$autores])){
                   
                    usort($texto[$autores], function ($a, $b) {
                        if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                            return 0;
                        }
                        return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                    });
                    
                    foreach($texto[$autores] as $autor){
                        
                        if(isset($autor['@attributes'])){
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }else{
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }
                    }
                }
                

                $aux_texto = [];
                $aux_texto['TITULO'] = $texto["DADOS-BASICOS-DO-TEXTO"]['@attributes']['TITULO-DO-TEXTO'] ?? '';
                $aux_texto['TIPO'] = $texto["DADOS-BASICOS-DO-TEXTO"]['@attributes']['NATUREZA'] ?? ''; //JORNAL OU REVISTA
                $aux_texto['SEQUENCIA-PRODUCAO'] = $texto['@attributes']["SEQUENCIA-PRODUCAO"] ?? ''; //JORNAL OU REVISTA
                $aux_texto['ANO'] = $texto["DADOS-BASICOS-DO-TEXTO"]['@attributes']['ANO-DO-TEXTO'] ?? ''; 
                $aux_texto['TITULO-DO-JORNAL-OU-REVISTA'] =  $texto["DETALHAMENTO-DO-TEXTO"]['@attributes']["TITULO-DO-JORNAL-OU-REVISTA"] ?? '';
                $aux_texto['DATA'] =  $texto["DETALHAMENTO-DO-TEXTO"]['@attributes']["DATA-DE-PUBLICACAO"] ?? '';
                $aux_texto['LOCAL-DE-PUBLICACAO'] =  $texto["DETALHAMENTO-DO-TEXTO"]['@attributes']["LOCAL-DE-PUBLICACAO"] ?? '';
                $aux_texto['VOLUME'] =  $texto["DETALHAMENTO-DO-TEXTO"]['@attributes']["VOLUME"] ?? '';
                $aux_texto['AUTORES'] =   $aux_autores;
                
                
                if($tipo == 'registros'){
                    if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
                }else if($tipo == 'anual'){
                    if($limit_ini != -1 &&  (int)$aux_texto['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
                }else if($tipo == 'periodo'){
                    if($limit_ini != -1 && 
                        (
                        (int)$aux_texto['ANO'] < $limit_ini ||
                        (int)$aux_texto['ANO'] > $limit_fim 
                        )
                    ) continue; 
                }

                array_push($textos_jornais_revistas, $aux_texto);
            }
        }else{
            return false;
        }
        usort($textos_jornais_revistas, function ($a, $b) {
            if(!isset($b['SEQUENCIA-PRODUCAO'])){
                return 0;
            }
            return (int)$b['SEQUENCIA-PRODUCAO'] - (int)$a['SEQUENCIA-PRODUCAO'];
        });

        return ($textos_jornais_revistas);
       
    
    }
    

    /**
    * Recebe o número USP e devolve array com os trabalhos em eventos/anais cadastrados no currículo Lattes
    * 
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros. 
    * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos; se for registros, irá retornar os últimos n livros; se for período, irá pegar os registros do ano entre limit_ini e limit_fim. Se limit_ini for igaul a -1, então retornará todos os registros
    * @param Integer $limit_fim = Se  o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim 
    * @return String|Bool
    */
    public static function getTrabalhosAnais($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['TRABALHOS-EM-EVENTOS'])) return false;
        $trabalhos_anais = [];
        
        if(isset($lattes['PRODUCAO-BIBLIOGRAFICA']['TRABALHOS-EM-EVENTOS']['TRABALHO-EM-EVENTOS'])){
            $i = 0;
            foreach($lattes['PRODUCAO-BIBLIOGRAFICA']['TRABALHOS-EM-EVENTOS']['TRABALHO-EM-EVENTOS'] as $anais){
                
                $i++;
                $autores = (!isset($anais['AUTORES']) && isset($anais[3])) ? 3 : 'AUTORES';
                
                $aux_autores = [];
                if(isset($anais[$autores])){
                   
                    usort($anais[$autores], function ($a, $b) {
                        if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                            return 0;
                        }
                        return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                    });
                    
                    foreach($anais[$autores] as $autor){
                        
                        if(isset($autor['@attributes'])){
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }else{
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }
                    }
                }
                
               
                $aux_anais = [];
                $aux_anais['TITULO'] = $anais["DADOS-BASICOS-DO-TRABALHO"]['@attributes']['TITULO-DO-TRABALHO'] ?? '';
                $aux_anais['TIPO'] = $anais["DADOS-BASICOS-DO-TRABALHO"]['@attributes']['NATUREZA'] ?? ''; 
                $aux_anais['SEQUENCIA-PRODUCAO'] = $anais['@attributes']["SEQUENCIA-PRODUCAO"] ?? ''; 
                $aux_anais['ANO'] = $anais["DADOS-BASICOS-DO-TRABALHO"]['@attributes']['ANO-DO-TRABALHO'] ?? ''; 
                $aux_anais['NOME-DO-EVENTO'] =  $anais["DETALHAMENTO-DO-TRABALHO"]['@attributes']["NOME-DO-EVENTO"] ?? '';
                $aux_anais['TITULO-DOS-ANAIS-OU-PROCEEDINGS'] =  $anais["DETALHAMENTO-DO-TRABALHO"]['@attributes']["TITULO-DOS-ANAIS-OU-PROCEEDINGS"] ?? '';
                $aux_anais['CIDADE-DO-EVENTO'] =  $anais["DETALHAMENTO-DO-TRABALHO"]['@attributes']["CIDADE-DO-EVENTO"] ?? '';
                $aux_anais['CIDADE-DA-EDITORA'] =  $anais["DETALHAMENTO-DO-TRABALHO"]['@attributes']["CIDADE-DA-EDITORA"] ?? '';
                $aux_anais['NOME-DA-EDITORA'] =  $anais["DETALHAMENTO-DO-TRABALHO"]['@attributes']["NOME-DA-EDITORA"] ?? '';
                $aux_anais['ANO-DE-REALIZACAO'] =  $anais["DETALHAMENTO-DO-TRABALHO"]['@attributes']["ANO-DE-REALIZACAO"] ?? '';
                $aux_anais['PAGINA-INICIAL'] =  $anais["DETALHAMENTO-DO-TRABALHO"]['@attributes']["PAGINA-INICIAL"] ?? '';
                $aux_anais['PAGINA-FINAL'] =  $anais["DETALHAMENTO-DO-TRABALHO"]['@attributes']["PAGINA-FINAL"] ?? '';
                $aux_anais['AUTORES'] =   $aux_autores;
                
                if($tipo == 'registros'){
                    if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
                }else if($tipo == 'anual'){
                    if($limit_ini != -1 &&  (int)$aux_anais['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
                }else if($tipo == 'periodo'){
                    if($limit_ini != -1 && 
                        (
                        (int)$aux_anais['ANO'] < $limit_ini ||
                        (int)$aux_anais['ANO'] > $limit_fim 
                        )
                    ) continue; 
                }


                array_push($trabalhos_anais, $aux_anais);
            }
        }else{
            return false;
        }
        usort($trabalhos_anais, function ($a, $b) {
            if(!isset($b['SEQUENCIA-PRODUCAO'])){
                return 0;
            }
            return (int)$b['SEQUENCIA-PRODUCAO'] - (int)$a['SEQUENCIA-PRODUCAO'];
        });

        return ($trabalhos_anais);
       
    
    }
    
    /**
    * Recebe o número USP e devolve array com os trabalhos técnicos cadastrados no currículo Lattes
    * 
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros. 
    * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos; se for registros, irá retornar os últimos n livros; se for período, irá pegar os registros do ano entre limit_ini e limit_fim. Se limit_ini for igaul a -1, então retornará todos os registros
    * @param Integer $limit_fim = Se  o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim 
    * @return String|Bool
    */
    public static function getTrabalhosTecnicos($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-TECNICA']['TRABALHO-TECNICO'])) return false;
        $trabalhos_tecnicos = [];
        $i = 0;
        foreach($lattes['PRODUCAO-TECNICA']['TRABALHO-TECNICO'] as $trabalho_tec){
            $i++;
            $autores = (!isset($trabalho_tec['AUTORES']) && isset($trabalho_tec[3])) ? 3 : 'AUTORES';
            
            $aux_autores = [];
            if(isset($trabalho_tec[$autores])){
                
                usort($trabalho_tec[$autores], function ($a, $b) {
                    if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                        return 0;
                    }
                    return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                });
                
                foreach($trabalho_tec[$autores] as $autor){
                    
                    if(isset($autor['@attributes'])){
                        array_push($aux_autores, [
                            "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? '',
                            "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? '',
                            "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? '',
                            ]);
                    }else{
                        array_push($aux_autores, [
                            "NOME-COMPLETO-DO-AUTOR" => $autor['NOME-COMPLETO-DO-AUTOR'] ?? '',
                            "NOME-PARA-CITACAO" => $autor['NOME-PARA-CITACAO'] ?? '',
                            "ORDEM-DE-AUTORIA" => $autor['ORDEM-DE-AUTORIA'] ?? '',
                            ]);
                    }
                }
            }
          
            
            $aux_trabalho_tec = [];
            $aux_trabalho_tec['TITULO'] = $trabalho_tec["DADOS-BASICOS-DO-TRABALHO-TECNICO"]['@attributes']['TITULO-DO-TRABALHO-TECNICO'] ?? '';
            $aux_trabalho_tec['TIPO'] = $trabalho_tec["DADOS-BASICOS-DO-TRABALHO-TECNICO"]['@attributes']['NATUREZA'] ?? ''; 
            $aux_trabalho_tec['SEQUENCIA-PRODUCAO'] = $trabalho_tec['@attributes']["SEQUENCIA-PRODUCAO"] ?? ''; 
            $aux_trabalho_tec['ANO'] = $trabalho_tec["DADOS-BASICOS-DO-TRABALHO-TECNICO"]['@attributes']['ANO'] ?? ''; 
            $aux_trabalho_tec['INSTITUICAO-FINANCIADORA'] =  $trabalho_tec["DETALHAMENTO-DO-TRABALHO-TECNICO"]['@attributes']["INSTITUICAO-FINANCIADORA"] ?? '';
            $aux_trabalho_tec['AUTORES'] =   $aux_autores;
            
            if($tipo == 'registros'){
                if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
            }else if($tipo == 'anual'){
                if($limit_ini != -1 &&  (int)$aux_trabalho_tec['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
            }else if($tipo == 'periodo'){
                if($limit_ini != -1 && 
                    (
                    (int)$aux_trabalho_tec['ANO'] < $limit_ini ||
                    (int)$aux_trabalho_tec['ANO'] > $limit_fim 
                    )
                ) continue; 
            }

            
            array_push($trabalhos_tecnicos, $aux_trabalho_tec);
        }
        
        usort($trabalhos_tecnicos, function ($a, $b) {
            if(!isset($b['SEQUENCIA-PRODUCAO'])){
                return 0;
            }
            return (int)$b['SEQUENCIA-PRODUCAO'] - (int)$a['SEQUENCIA-PRODUCAO'];
        });

        return ($trabalhos_tecnicos);
       
    
    }
    

     /**
    * Recebe o número USP e devolve array com as "outras" produções técnicas cadastradas no currículo Lattes, identidicadas como 'Demais tipos de produção técnica'
    * 
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros. 
    * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos; se for registros, irá retornar os últimos n livros; se for período, irá pegar os registros do ano entre limit_ini e limit_fim. Se limit_ini for igaul a -1, então retornará todos os registros
    * @param Integer $limit_fim = Se  o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim 
    * @return String|Bool
    */
    public static function getOutrasProducoesTecnicas($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA'])) return false;
        $outras = [];

        if(isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['OUTRA-PRODUCAO-TECNICA'])){
            $i = 0;
            foreach($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['OUTRA-PRODUCAO-TECNICA'] as $outro){
                $i++;
                $autores = (!isset($outro['AUTORES']) && isset($outro[3])) ? 3 : 'AUTORES';
                
                $aux_autores = [];
                if(isset($outro[$autores])){
                   
                    usort($outro[$autores], function ($a, $b) {
                        if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                            return 0;
                        }
                        return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                    });
                    
                    foreach($outro[$autores] as $autor){
                        
                        if(isset($autor['@attributes'])){
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }else{
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }
                    }
                }
               
                $aux_outros = [];
                $aux_outros['TITULO'] = $outro["DADOS-BASICOS-DE-OUTRA-PRODUCAO-TECNICA"]['@attributes']['TITULO'] ?? '';
                $aux_outros['NATUREZA'] = $outro["DADOS-BASICOS-DE-OUTRA-PRODUCAO-TECNICA"]['@attributes']['NATUREZA'] ?? ''; 
                $aux_outros['SEQUENCIA-PRODUCAO'] = $outro['@attributes']["SEQUENCIA-PRODUCAO"] ?? '';
                $aux_outros['ANO'] = $outro["DADOS-BASICOS-DE-OUTRA-PRODUCAO-TECNICA"]['@attributes']['ANO'] ?? ''; 
                $aux_outros['AUTORES'] =   $aux_autores;
                
                
                
                if($tipo == 'registros'){
                    if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
                }else if($tipo == 'anual'){
                    if($limit_ini != -1 &&  (int)$aux_outros['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
                }else if($tipo == 'periodo'){
                    if($limit_ini != -1 && 
                        (
                        (int)$aux_outros['ANO'] < $limit_ini ||
                        (int)$aux_outros['ANO'] > $limit_fim 
                        )
                    ) continue; 
                }
                
                array_push($outras, $aux_outros);
            }
        }
        
        return ($outras);
       
    }
    
    
    /**
    * Recebe o número USP e devolve array com as "outras" produções técnicas cadastradas no currículo Lattes, identidicadas como 'Demais tipos de produção técnica'
    * 
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros. 
    * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos; se for registros, irá retornar os últimos n livros; se for período, irá pegar os registros do ano entre limit_ini e limit_fim. Se limit_ini for igaul a -1, então retornará todos os registros
    * @param Integer $limit_fim = Se  o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim 
    * @return String|Bool
    */
    public static function getOrganizacaoEvento($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA'])) return false;
        $eventos = [];

        if(isset($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['ORGANIZACAO-DE-EVENTO'])){
            $i = 0;
            foreach($lattes['PRODUCAO-TECNICA']['DEMAIS-TIPOS-DE-PRODUCAO-TECNICA']['ORGANIZACAO-DE-EVENTO'] as $evento){
                $i++;
                $autores = (!isset($evento['AUTORES']) && isset($evento[3])) ? 3 : 'AUTORES';
                
                $aux_autores = [];
                if(isset($evento[$autores])){
                   
                    usort($evento[$autores], function ($a, $b) {
                        if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                            return 0;
                        }
                        return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                    });
                    
                    foreach($evento[$autores] as $autor){
                        
                        if(isset($autor['@attributes'])){
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }else{
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }
                    }
                }
                $aux_evento = [];
                $aux_evento['TITULO'] = $evento["DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO"]['@attributes']['TITULO'] ?? '';
                $aux_evento['ANO'] = $evento["DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO"]['@attributes']['ANO'] ?? ''; 
                $aux_evento['TIPO'] = $evento["DADOS-BASICOS-DA-ORGANIZACAO-DE-EVENTO"]['@attributes']['TIPO'] ?? '';
                $aux_evento['INSTITUICAO-PROMOTORA'] = $evento["DETALHAMENTO-DA-ORGANIZACAO-DE-EVENTO"]['@attributes']['INSTITUICAO-PROMOTORA'] ?? ''; 
                $aux_evento['SEQUENCIA-PRODUCAO'] = $evento['@attributes']["SEQUENCIA-PRODUCAO"] ?? '';
                $aux_evento['AUTORES'] =   $aux_autores;
                
                
                
                
                if($tipo == 'registros'){
                    if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
                }else if($tipo == 'anual'){
                    if($limit_ini != -1 &&  (int)$aux_evento['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
                }else if($tipo == 'periodo'){
                    if($limit_ini != -1 && 
                        (
                        (int)$aux_evento['ANO'] < $limit_ini ||
                        (int)$aux_evento['ANO'] > $limit_fim 
                        )
                    ) continue; 
                }
                
                array_push($eventos, $aux_evento);
            }
        }
        
        return ($eventos);
       
    }


    
    /**
    * Recebe o número USP e devolve array com as "outras" produções bibliográficas, uma subcategoria das produções, cadastrados no currículo Lattes
    * 
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros. 
    * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos; se for registros, irá retornar os últimos n livros; se for período, irá pegar os registros do ano entre limit_ini e limit_fim. Se limit_ini for igaul a -1, então retornará todos os registros
    * @param Integer $limit_fim = Se  o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim 
    * @return String|Bool
    */
    public static function getOutrasProducoesBibliograficas($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA'])) return false;
        $outras = [];

        if(isset($lattes['PRODUCAO-BIBLIOGRAFICA']['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA']['OUTRA-PRODUCAO-BIBLIOGRAFICA'])){
            $i = 0;
            foreach($lattes['PRODUCAO-BIBLIOGRAFICA']['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA']['OUTRA-PRODUCAO-BIBLIOGRAFICA'] as $outro){
                
                $i++;
                $autores = (!isset($outro['AUTORES']) && isset($outro[3])) ? 3 : 'AUTORES';
                
                $aux_autores = [];
                if(isset($outro[$autores])){
                   
                    usort($outro[$autores], function ($a, $b) {
                        if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                            return 0;
                        }
                        return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                    });
                    
                    foreach($outro[$autores] as $autor){
                        
                        if(isset($autor['@attributes'])){
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }else{
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }
                    }
                }
                
                $aux_outros = [];
                $aux_outros['TITULO'] = $outro["DADOS-BASICOS-DE-OUTRA-PRODUCAO"]['@attributes']['TITULO'] ?? '';
                $aux_outros['TIPO'] = $outro["DADOS-BASICOS-DE-OUTRA-PRODUCAO"]['@attributes']['NATUREZA'] ?? ''; 
                $aux_outros['SEQUENCIA-PRODUCAO'] = $outro['@attributes']["SEQUENCIA-PRODUCAO"] ?? '';
                $aux_outros['ANO'] = $outro["DADOS-BASICOS-DE-OUTRA-PRODUCAO"]['@attributes']['ANO'] ?? ''; 
                $aux_outros['EDITORA'] =  $outro["DETALHAMENTO-DE-OUTRA-PRODUCAO"]['@attributes']["EDITORA"] ?? '';
                $aux_outros['CIDADE-DA-EDITORA'] =  $outro["DETALHAMENTO-DE-OUTRA-PRODUCAO"]['@attributes']["CIDADE-DA-EDITORA"] ?? '';
                $aux_outros['AUTORES'] =   $aux_autores;
                
                
                
                if($tipo == 'registros'){
                    if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
                }else if($tipo == 'anual'){
                    if($limit_ini != -1 &&  (int)$aux_outros['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
                }else if($tipo == 'periodo'){
                    if($limit_ini != -1 && 
                        (
                        (int)$aux_outros['ANO'] < $limit_ini ||
                        (int)$aux_outros['ANO'] > $limit_fim 
                        )
                    ) continue; 
                }

                array_push($outras, $aux_outros);
            }
        }
        if(isset($lattes['PRODUCAO-BIBLIOGRAFICA']['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA']['PREFACIO-POSFACIO'])){
            $i = 0;
            foreach($lattes['PRODUCAO-BIBLIOGRAFICA']['DEMAIS-TIPOS-DE-PRODUCAO-BIBLIOGRAFICA']['PREFACIO-POSFACIO'] as $prefacio_posfacio){
                
                $i++;

                $autores = (!isset($prefacio_posfacio['AUTORES']) && isset($prefacio_posfacio[3])) ? 3 : 'AUTORES';
                
                $aux_autores = [];
                if(isset($prefacio_posfacio[$autores])){
                   
                    usort($prefacio_posfacio[$autores], function ($a, $b) {
                        if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                            return 0;
                        }
                        return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                    });
                    
                    foreach($prefacio_posfacio[$autores] as $autor){
                        
                        if(isset($autor['@attributes'])){
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }else{
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['NOME-COMPLETO-DO-AUTOR'] ?? '',
                                "NOME-PARA-CITACAO" => $autor['NOME-PARA-CITACAO'] ?? '',
                                "ORDEM-DE-AUTORIA" => $autor['ORDEM-DE-AUTORIA'] ?? '',
                                ]);
                        }
                    }
                }
                
                
                $aux_prefacio_posfacio = [];
                $aux_prefacio_posfacio['TITULO'] = $prefacio_posfacio["DADOS-BASICOS-DO-PREFACIO-POSFACIO"]['@attributes']['TITULO'] ?? '';
                $aux_prefacio_posfacio['TIPO'] = isset($prefacio_posfacio["DADOS-BASICOS-DO-PREFACIO-POSFACIO"]['@attributes']['TIPO']) ? 'Prefácio, Pósfacio/' . ucfirst(strtolower($prefacio_posfacio["DADOS-BASICOS-DO-PREFACIO-POSFACIO"]['@attributes']['TIPO'])) : ''; 
                $aux_prefacio_posfacio['SEQUENCIA-PRODUCAO'] = $prefacio_posfacio['@attributes']["SEQUENCIA-PRODUCAO"] ?? '';
                $aux_prefacio_posfacio['ANO'] = $prefacio_posfacio["DADOS-BASICOS-DO-PREFACIO-POSFACIO"]['@attributes']['ANO'] ?? ''; 
                $aux_prefacio_posfacio['CIDADE-DA-EDITORA'] =  $prefacio_posfacio["DETALHAMENTO-DO-PREFACIO-POSFACIO"]['@attributes']["CIDADE-DA-EDITORA"] ?? '';
                $aux_prefacio_posfacio['EDITORA'] =  $prefacio_posfacio["DETALHAMENTO-DO-PREFACIO-POSFACIO"]['@attributes']["EDITORA-DO-PREFACIO-POSFACIO"] ?? '';
                $aux_prefacio_posfacio['AUTORES'] =   $aux_autores;
                
                
                if($tipo == 'registros'){
                    if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
                }else if($tipo == 'anual'){
                    if($limit_ini != -1 &&  (int)$aux_prefacio_posfacio['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
                }else if($tipo == 'periodo'){
                    if($limit_ini != -1 && 
                        (
                        (int)$aux_prefacio_posfacio['ANO'] < $limit_ini ||
                        (int)$aux_prefacio_posfacio['ANO'] > $limit_fim 
                        )
                    ) continue; 
                }

                array_push($outras, $aux_prefacio_posfacio);
            }
        }
        usort($outras, function ($a, $b) {
            if(!isset($b['SEQUENCIA-PRODUCAO'])){
                return 0;
            }
            return (int)$b['SEQUENCIA-PRODUCAO'] - (int)$a['SEQUENCIA-PRODUCAO'];
        });

        return ($outras);
       
    }
   

      /**
    * Recebe o número USP e devolve array com os 5 últimos capítulos de livros publicados cadastrados no currículo Lattes,
    * com o respectivo título do capítulo, título do livro, número de volumes, página inicial e final do capítulo, ano e nome da editora.
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Valores possíveis para determinar o limite: 'anual' e 'registros', 'periodo'. Default: últimos 5 registros. 
    * @param Integer $limit_ini = Limite de retorno conforme o tipo. Se for anual, o limit vai pegar os registros dos 'n' útimos anos; se for registros, irá retornar os últimos n livros; se for período, irá pegar os registros do ano entre limit_ini e limit_fim. Se limit_ini for igaul a -1, então retornará todos os registros
    * @param Integer $limit_fim = Se  o tipo for periodo, irá pegar os registros do ano entre limit_ini e limit_fim 
    * @return String|Bool
    */
    public static function getCapitulosLivros($codpes, $lattes_array = null, $tipo = 'registros', $limit_ini = 5, $limit_fim = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS'])) return false;
        $capitulos = $lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS'];
        
        if(array_key_exists('CAPITULOS-DE-LIVROS-PUBLICADOS',$capitulos)){
            $capitulos = $lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS']['CAPITULOS-DE-LIVROS-PUBLICADOS']['CAPITULO-DE-LIVRO-PUBLICADO'];
            if(!isset($capitulos)){
                return false;
            } else{
                //ordena em ordem decrescente.
                usort($capitulos, function ($a, $b) {
                    if(!isset($b['@attributes']['SEQUENCIA-PRODUCAO'])){
                        return 0;
                    }
                    return (int)$b['@attributes']['SEQUENCIA-PRODUCAO'] - (int)$a['@attributes']['SEQUENCIA-PRODUCAO'];
                });
            }
            $i = 0;
            $ultimos_capitulos = [];
            if(isset($capitulos[1]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'])){//quando tem apenas uma produção
               
                $autores = (!isset($capitulos['AUTORES']) && isset($capitulos[3])) ? 3 : 'AUTORES';
                
                $aux_autores = [];
                if(isset($capitulos[$autores])){
                    
                    usort($capitulos[$autores], function ($a, $b) {
                        if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                            return 0;
                        }
                        return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                    });
                    
                    foreach($capitulos[$autores] as $autor){    
                        array_push($aux_autores, [
                            "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? $autor['NOME-COMPLETO-DO-AUTOR'],
                            "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? $autor['NOME-PARA-CITACAO'],
                            "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? $autor['ORDEM-DE-AUTORIA'],
                            ]);
                    }
                    
                }

                $aux_capitulo = [
                    'TITULO-DO-CAPITULO-DO-LIVRO' => $capitulos[1]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'] ?? '',
                    'TITULO-DO-LIVRO' => $capitulos[2]['@attributes']['TITULO-DO-LIVRO'] ?? '',
                    'NUMERO-DE-VOLUMES' => $capitulos[2]['@attributes']['NUMERO-DE-VOLUMES'] ?? '',
                    'PAGINA-INICIAL' => $capitulos[2]['@attributes']['PAGINA-INICIAL'] ?? '',
                    'PAGINA-FINAL' => $capitulos[2]['@attributes']['PAGINA-FINAL'] ?? '',
                    'ANO' => $capitulos[1]['@attributes']['ANO'] ?? '',
                    'NOME-DA-EDITORA' => $capitulos[2]['@attributes']['NOME-DA-EDITORA'] ?? '',
                    'AUTORES' => $aux_autores
                ]; 
                if($tipo == 'anual'){
                    if($limit_ini != -1 &&  (int)$aux_capitulo['ANO'] !=  $limit_ini ) return false;
                }else if($tipo == 'periodo'){
                    if($limit_ini != -1 && 
                        (
                        (int)$aux_capitulo['ANO'] < $limit_ini ||
                        (int)$aux_capitulo['ANO'] > $limit_fim 
                        )
                    )  return false;
                }
                array_push($ultimos_capitulos, $aux_capitulo);
            }else{
                $i = 0;
                foreach($capitulos as $val){
                    $i++;
                    $dados_basicos = (!isset($val['DADOS-BASICOS-DO-CAPITULO']) && isset($val[1])) ? 1 : 'DADOS-BASICOS-DO-CAPITULO';
                    $detalhamento = (!isset($val['DETALHAMENTO-DO-CAPITULO']) && isset($val[2])) ? 2 : 'DETALHAMENTO-DO-CAPITULO';
                    $autores = (!isset($val['AUTORES']) && isset($val[3])) ? 3 : 'AUTORES';
                
                    $aux_autores = [];
                    if(isset($val[$autores])){
                        
                        usort($val[$autores], function ($a, $b) {
                            if(!isset($b['@attributes']['ORDEM-DE-AUTORIA'])){
                                return 0;
                            }
                            return (int)$a['@attributes']['ORDEM-DE-AUTORIA'] - (int)$b['@attributes']['ORDEM-DE-AUTORIA'];
                        });
                        
                        
                        foreach($val[$autores] as $autor){
                            
                            array_push($aux_autores, [
                                "NOME-COMPLETO-DO-AUTOR" => $autor['@attributes']['NOME-COMPLETO-DO-AUTOR'] ?? $autor['NOME-COMPLETO-DO-AUTOR'],
                                "NOME-PARA-CITACAO" => $autor['@attributes']['NOME-PARA-CITACAO'] ?? $autor['NOME-PARA-CITACAO'],
                                "ORDEM-DE-AUTORIA" => $autor['@attributes']['ORDEM-DE-AUTORIA'] ?? $autor['ORDEM-DE-AUTORIA'],
                                ]);
                        }
                        
                    }

                    if(isset($val[$dados_basicos]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'])){
                        $aux_capitulo = [
                            'TITULO-DO-CAPITULO-DO-LIVRO' => $val[$dados_basicos]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'] ?? '',
                            'TITULO-DO-LIVRO' => $val[$detalhamento]['@attributes']['TITULO-DO-LIVRO'] ?? '',
                            'NUMERO-DE-VOLUMES' => $val[$detalhamento]['@attributes']['NUMERO-DE-VOLUMES'] ?? '',
                            'PAGINA-INICIAL' => $val[$detalhamento]['@attributes']['PAGINA-INICIAL'] ?? '',
                            'PAGINA-FINAL' => $val[$detalhamento]['@attributes']['PAGINA-FINAL'] ?? '',
                            'ANO' => $val[$dados_basicos]['@attributes']['ANO'] ?? '',
                            'NOME-DA-EDITORA' => $val[$detalhamento]['@attributes']['NOME-DA-EDITORA'] ?? '',
                            'AUTORES' => $aux_autores
                        ];

                        if($tipo == 'registros'){
                            if($limit_ini != -1 && $i > $limit_ini) continue;  //-1 retorna tudo
                        }else if($tipo == 'anual'){
                            if($limit_ini != -1 &&  (int)$aux_capitulo['ANO'] !=  $limit_ini ) continue; //se for diferente do ano determinado, pula para o próximo
                        }else if($tipo == 'periodo'){
                            if($limit_ini != -1 && 
                                (
                                (int)$aux_capitulo['ANO'] < $limit_ini ||
                                (int)$aux_capitulo['ANO'] > $limit_fim 
                                )
                            ) continue; 
                        }

                        array_push($ultimos_capitulos, $aux_capitulo);
                    }
                }
            }
            return $ultimos_capitulos;
        } else return false;
    }
    
    /**
    * Recebe o número USP e devolve array com o título e ano da tese especificada (MESTRADO ou DOUTORADO), cadastrada no currículo Lattes.
    * Retorna o título da tese e as palavras-chaves.
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Tipo da tese: DOUTORADO ou MESTRADO, o valor default é DOUTORADO
    * @return String|Bool
    */
    public static function getTeses($codpes, $tipo = 'DOUTORADO', $lattes_array = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
       
        if(!$lattes && !isset($lattes['DADOS-GERAIS'])) return false;
    
        $teses = $lattes['DADOS-GERAIS'];
        
        if(array_key_exists('FORMACAO-ACADEMICA-TITULACAO',$teses)){
            
            if(!isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO'][$tipo])) return false;
            $teses = $lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO'][$tipo];
            $nome_teses = [];
            foreach($teses as $p){
                
                $palavras_chaves = '';
                for ($i=1; $i <= 6; $i++) { 
                    $key_i = 'PALAVRA-CHAVE-'. $i;
                    if(isset($teses['PALAVRAS-CHAVE']['@attributes'][$key_i])){
                        $palavras_chaves .= $teses['PALAVRAS-CHAVE']['@attributes'][$key_i].'; ';
                    }
                    else if(isset($p['PALAVRAS-CHAVE']['@attributes'][$key_i])){
                        $palavras_chaves .= $p['PALAVRAS-CHAVE']['@attributes'][$key_i].'; ';
                    }
                }
                $palavras_chaves = str_replace(' ; ', '', $palavras_chaves);
                $palavras_chaves = str_replace(';;', '', $palavras_chaves);
                if(isset($p['@attributes']['TITULO-DA-DISSERTACAO-TESE'])){
                    $titulo = $p['@attributes']['TITULO-DA-DISSERTACAO-TESE'];
                }else if(isset($p['TITULO-DA-DISSERTACAO-TESE'])){
                    $titulo = $p['TITULO-DA-DISSERTACAO-TESE'];
                }else{
                    $titulo = '';
                }
                if(isset($p['@attributes']['ANO-DE-OBTENCAO-DO-TITULO'])){
                    $ano = $p['@attributes']['ANO-DE-OBTENCAO-DO-TITULO'];
                }else if(isset($p['ANO-DE-OBTENCAO-DO-TITULO'])){
                    $ano = $p['ANO-DE-OBTENCAO-DO-TITULO'];
                }else{
                    $ano = '';
                }
                if(strlen($titulo) > 0){
                    array_push($nome_teses, ['TITULO'=> $titulo, 'PALAVRAS-CHAVE' => $palavras_chaves, 'ANO-DE-OBTENCAO-DO-TITULO' => $ano]);
                }
                
            }  
        return count($nome_teses) > 0 ? $nome_teses : false ;
            
        }
        else return false;
    }


    /**
    * Recebe o número USP e retorna array com o título da tese de Livre-Docência, cadastrada no currículo Lattes.
    * @param Integer $codpes = Número USP
    * @return Array|Bool
    */
    public static function getLivreDocencia($codpes, $lattes_array = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
   
        
        if(!$lattes && !isset($lattes['DADOS-GERAIS'])) return false;
    
        $teses = $lattes['DADOS-GERAIS'];
        
        if(array_key_exists('FORMACAO-ACADEMICA-TITULACAO',$teses)){
            
            if(!isset($lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['LIVRE-DOCENCIA'])) return false;
            $teses = $lattes['DADOS-GERAIS']['FORMACAO-ACADEMICA-TITULACAO']['LIVRE-DOCENCIA'];
            $nome_teses = [];
            foreach($teses as $p){
                if(isset($p['@attributes']['TITULO-DO-TRABALHO'])){
                    $titulo = $p['@attributes']['TITULO-DO-TRABALHO'];
                }else if(isset($p['TITULO-DO-TRABALHO'])){
                    $titulo = $p['TITULO-DO-TRABALHO'];
                }else{
                    $titulo = '';
                }
                if(strlen($titulo) > 0){
                    array_push($nome_teses, $titulo);
                }
                
            }  
        return count($nome_teses) > 0 ? $nome_teses : false ;
            
        }
        else return false;
    }

     /**
    * Recebe o número USP e retorna array com os título das teses de Mestrado onde o docente particiou como integrante da banca avaliadora.
    * @param Integer $codpes = Número USP
    * @return Array|Bool
    */
    public static function getBancaMestrado($codpes, $lattes_array){
        $lattes = $lattes_array ?? self::getArray($codpes);
       
        if(!$lattes && !isset($lattes['DADOS-COMPLEMENTARES'])) return false;
        $bancas = $lattes['DADOS-COMPLEMENTARES'];

        if(array_key_exists('PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO',$bancas)){
            if(!isset($lattes['DADOS-COMPLEMENTARES']['PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO']['PARTICIPACAO-EM-BANCA-DE-MESTRADO'])) return false;
            $bancas = $lattes['DADOS-COMPLEMENTARES']['PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO']['PARTICIPACAO-EM-BANCA-DE-MESTRADO'];
            $nome_bancas = [];
            foreach($bancas as $b){
                if(!isset($b['DADOS-BASICOS-DA-PARTICIPACAO-EM-BANCA-DE-MESTRADO']['@attributes']['TITULO'])){
                    return false;
                } else
                array_push($nome_bancas, $b['DADOS-BASICOS-DA-PARTICIPACAO-EM-BANCA-DE-MESTRADO']['@attributes']['TITULO']);
            } 
        return $nome_bancas;
        }
        else return false;
    }

      /**
    * Recebe o número USP e retorna array com os título das teses de Doutorado onde o docente particiou como integrante da banca avaliadora.
    * @param Integer $codpes = Número USP
    * @return Array|Bool
    */
    public static function getBancaDoutorado($codpes, $lattes_array = null){
        $lattes = $lattes_array ?? self::getArray($codpes);
       
        if(!$lattes && !isset($lattes['DADOS-COMPLEMENTARES'])) return false;
        $bancas = $lattes['DADOS-COMPLEMENTARES'];

        if(array_key_exists('PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO',$bancas)){
            if(!isset($lattes['DADOS-COMPLEMENTARES']['PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO']['PARTICIPACAO-EM-BANCA-DE-DOUTORADO'])) return false;
            $bancas = $lattes['DADOS-COMPLEMENTARES']['PARTICIPACAO-EM-BANCA-TRABALHOS-CONCLUSAO']['PARTICIPACAO-EM-BANCA-DE-DOUTORADO'];
            $nome_bancas = [];
            foreach($bancas as $b){
                if(!isset($b['DADOS-BASICOS-DA-PARTICIPACAO-EM-BANCA-DE-DOUTORADO']['@attributes']['TITULO'])){
                    return false;
                } else{
                    $aux = $b['DADOS-BASICOS-DA-PARTICIPACAO-EM-BANCA-DE-DOUTORADO']['@attributes']['TITULO'] ?? '';
                    if(isset($b['DETALHAMENTO-DA-PARTICIPACAO-EM-BANCA-DE-DOUTORADO']['@attributes']['NOME-DO-CANDIDATO'])){
                        $aux .= "\n";
                        $aux .= $b['DETALHAMENTO-DA-PARTICIPACAO-EM-BANCA-DE-DOUTORADO']['@attributes']['NOME-DO-CANDIDATO'] ?? '' ;
                    }
                    
                    array_push($nome_bancas, $aux);
                }
            } 
        return $nome_bancas;
        }
        else return false;
    }
}
