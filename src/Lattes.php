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
    public static function getPremios($codpes){
        $lattes = self::getArray($codpes);
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
    public static function getResumoCV($codpes, $idioma = 'pt'){
        $lattes = self::getArray($codpes);

        if(!$lattes) return false;

        $campo = 'TEXTO-RESUMO-CV-RH';
        if(strtolower($idioma) == 'en') $campo .= '-EN'; 
        $resumo_cv = isset($lattes['DADOS-GERAIS']['RESUMO-CV']['@attributes'][$campo]) 
                    ? $lattes['DADOS-GERAIS']['RESUMO-CV']['@attributes'][$campo]
                    : false;
        
        return $resumo_cv;
    }

     /**
    * Recebe o número USP e devolve array com os últimos artigos cadastrados no currículo Lattes,
    * com o respectivo título do artigo, nome da revista ou períodico, volume, número de páginas e ano de publicação
    *  
    * @param Integer $codpes = Número USP
    * @param Integer $limit = Número de artigos a serem retornados, se não preenchido, o valor default é 5
    * @param String $tipo = Valores possíveis para determinar o limite: 'ano' e 'registro'. Default: últimos 5 anos. 
    * @return String|Bool
    */
    public static function getArtigos($codpes, $limit = 5, $tipo = 'ano'){
        $lattes = self::getArray($codpes);
        if(!$lattes && !isset($lattes['PRODUCAO-BIBLIOGRAFICA'])) return false;
        $artigos = $lattes['PRODUCAO-BIBLIOGRAFICA'];
        $limit_ano = date("Y") - $limit;


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
                    'AUTORES' => $aux_autores
                ];

                if($tipo == 'registro'){
                    if($limit != -1 && $i > ($limit - 1) ) break;  //-1 retorna tudo
                }else if($tipo == 'ano'){
                    if($limit != -1 &&  $aux_artigo['ANO'] <  $limit_ano ) break;
                }
                $i++;

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
    public static function getLinhasPesquisa($codpes){
        $lattes = self::getArray($codpes);
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
    * Recebe o número USP e devolve array com os 5 últimos livros publicados cadastrados no currículo Lattes,
    * com o respectivo título do livro, ano, número de páginas e nome da editora
    *  
    * @param Integer $codpes = Número USP
    * @param Integer $limit = Número de livros a serem retornados, se não preenchido, o valor default é 5
    * @param String $tipo = Valores possíveis para determinar o limite: 'ano' e 'registro'. Default: últimos 5 anos. 
    * @return String|Bool
    */
    public static function getLivrosPublicados($codpes, $limit = 5, $tipo = 'ano'){
        $lattes = self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS'])) return false;
        $limit_ano = date("Y") - $limit;

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
                    'AUTORES' => $aux_autores
                ];
                

                if($tipo == 'registro'){
                    if($limit != -1 && $i > ($limit - 1) ) break;  //-1 retorna tudo
                }else if($tipo == 'ano'){
                    if($limit != -1 &&  $aux_livro['ANO'] <  $limit_ano ) break;
                }
                $i++;
                array_push($ultimos_livros, $aux_livro);
            }
            
            return $ultimos_livros;
        } else return false;
    }

    /**
    * Recebe o número USP e devolve array com os 5 últimos capítulos de livros publicados cadastrados no currículo Lattes,
    * com o respectivo título do capítulo, título do livro, número de volumes, página inicial e final do capítulo, ano e nome da editora.
    *  
    * @param Integer $codpes = Número USP
    * @param Integer $limit = Número de capítulos publicados a serem retornados, se não preenchido, o valor default é 5
    * @return String|Bool
    */
    public static function getCapitulosLivros($codpes, $limit = 5){
        $lattes = self::getArray($codpes);
        if(!$lattes) return false;
        if(!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS'])) return false;
        $capitulos = $lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS'];
        
        if(array_key_exists('CAPITULOS-DE-LIVROS-PUBLICADOS',$capitulos)){
            $capitulos = $lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS']['CAPITULOS-DE-LIVROS-PUBLICADOS']['CAPITULO-DE-LIVRO-PUBLICADO'];
                if(!isset($lattes['PRODUCAO-BIBLIOGRAFICA']['LIVROS-E-CAPITULOS']['CAPITULOS-DE-LIVROS-PUBLICADOS']['CAPITULO-DE-LIVRO-PUBLICADO'])){
                    return false;
                } else
            //ordena em ordem decrescente.
            usort($capitulos, function ($a, $b) {
                if(!isset($b['@attributes']['SEQUENCIA-PRODUCAO'])){
                    return 0;
                }
                return (int)$b['@attributes']['SEQUENCIA-PRODUCAO'] - (int)$a['@attributes']['SEQUENCIA-PRODUCAO'];
            });
            $i = 0;
            $ultimos_capitulos = [];
            if(isset($capitulos[1]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'])){
                $aux_capitulo = [
                    'TITULO-DO-CAPITULO-DO-LIVRO' => $capitulos[1]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'] ?? '',
                    'TITULO-DO-LIVRO' => $capitulos[2]['@attributes']['TITULO-DO-LIVRO'] ?? '',
                    'NUMERO-DE-VOLUMES' => $capitulos[2]['@attributes']['NUMERO-DE-VOLUMES'] ?? '',
                    'PAGINA-INICIAL' => $capitulos[2]['@attributes']['PAGINA-INICIAL'] ?? '',
                    'PAGINA-FINAL' => $capitulos[2]['@attributes']['PAGINA-FINAL'] ?? '',
                    'ANO' => $capitulos[1]['@attributes']['ANO'] ?? '',
                    'NOME-DA-EDITORA' => $capitulos[2]['@attributes']['NOME-DA-EDITORA'] ?? '',
                ];
                array_push($ultimos_capitulos, $aux_capitulo);
            }else{
                foreach($capitulos as $val){
                    if($limit != -1 && $i > ($limit - 1) ) break; $i++; //-1 retorna tudo
                    $dados_basicos = (!isset($val['DADOS-BASICOS-DO-CAPITULO']) && isset($val[1])) ? 1 : 'DADOS-BASICOS-DO-CAPITULO';
                    $detalhamento = (!isset($val['DETALHAMENTO-DO-CAPITULO']) && isset($val[2])) ? 2 : 'DETALHAMENTO-DO-CAPITULO';
                
                    if(isset($val[$dados_basicos]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'])){
                        $aux_capitulo = [
                            'TITULO-DO-CAPITULO-DO-LIVRO' => $val[$dados_basicos]['@attributes']['TITULO-DO-CAPITULO-DO-LIVRO'] ?? '',
                            'TITULO-DO-LIVRO' => $val[$detalhamento]['@attributes']['TITULO-DO-LIVRO'] ?? '',
                            'NUMERO-DE-VOLUMES' => $val[$detalhamento]['@attributes']['NUMERO-DE-VOLUMES'] ?? '',
                            'PAGINA-INICIAL' => $val[$detalhamento]['@attributes']['PAGINA-INICIAL'] ?? '',
                            'PAGINA-FINAL' => $val[$detalhamento]['@attributes']['PAGINA-FINAL'] ?? '',
                            'ANO' => $val[$dados_basicos]['@attributes']['ANO'] ?? '',
                            'NOME-DA-EDITORA' => $val[$detalhamento]['@attributes']['NOME-DA-EDITORA'] ?? '',
                        ];
                        array_push($ultimos_capitulos, $aux_capitulo);
                    }
                }
            }
            return $ultimos_capitulos;
        } else return false;
    }
    

    /**
    * Recebe o número USP e devolve array com a tese especificada (MESTRADO ou DOUTORADO), cadastrada no currículo Lattes.
    * Retorna o título da tese e as palavras-chaves.
    *  
    * @param Integer $codpes = Número USP
    * @param String $tipo = Tipo da tese: DOUTORADO ou MESTRADO, o valor default é DOUTORADO
    * @return String|Bool
    */
    public static function getTeses($codpes, $tipo = 'DOUTORADO'){
        $lattes = self::getArray($codpes);
       
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
                if(strlen($titulo) > 0){
                    array_push($nome_teses, ['TITULO'=> $titulo, 'PALAVRAS-CHAVE' => $palavras_chaves]);
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
    public static function getLivreDocencia($codpes){
        $lattes = self::getArray($codpes);
   
        
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
    public static function getBancaMestrado($codpes){
        $lattes = self::getArray($codpes);
       
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
    public static function getBancaDoutorado($codpes){
        $lattes = self::getArray($codpes);
       
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
