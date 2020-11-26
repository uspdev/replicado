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
        putenv('REPLICADO_SYBASE=0');
        $query = "SELECT imgarqxml from DIM_PESSOA_XMLUSP WHERE codpes = convert(int,:codpes)";
        $param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetch($query, $param);
        
        if(!empty($result)) return $result['imgarqxml'];
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
        $id = self::id($codpes);
        if(!$id) return false;

        return Uteis::unzip(self::getZip($codpes));
    }

    /**
     * Recebe o número USP e devolve json do lattes
     * 
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function getJson($codpes){
        $id = self::id($codpes);
        if(!$id) return false;
        $xml_string = Uteis::unzip(self::getZip($codpes));
        $xml = simplexml_load_string($xml_string);
        return json_encode($xml);
    }

    /**
     * Recebe o número USP e devolve array do lattes
     * 
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function getArray($codpes){
        $id = self::id($codpes);
        if(!$id) return false;
        return json_decode(self::getJson($codpes),TRUE);
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
        $premios = $lattes['DADOS-GERAIS'];
        if(array_key_exists('PREMIOS-TITULOS',$premios)){
            $premios = $lattes['DADOS-GERAIS']['PREMIOS-TITULOS']['PREMIO-TITULO'];
            $nome_premios = [];
            foreach($premios as $p){
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
     * @param String $idioma 
     * @return String|Bool
     * 
     * Valores aceitos para idioma: 'pt' (português) e 'en' (inglês)
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
     * Recebe o número USP e devolve array dos 5 últimos artigos cadastros no currículo Lattes,
     * com o respectivo título do artigo, nome da revista ou períodico, volume, número de páginas e ano de publicação
     * 
     * @param Integer $codpes
     * @return String|Bool
     */
    public static function getArtigos($codpes){
        $lattes = self::getArray($codpes);
        
        if(!$lattes) return false;

        $artigos = $lattes['PRODUCAO-BIBLIOGRAFICA'];
        if(array_key_exists('ARTIGOS-PUBLICADOS',$artigos)){
        $artigos = $lattes['PRODUCAO-BIBLIOGRAFICA']['ARTIGOS-PUBLICADOS']['ARTIGO-PUBLICADO'];
        usort($artigos, function ($a, $b) {
            return (int)$b['@attributes']['SEQUENCIA-PRODUCAO'] - (int)$a['@attributes']['SEQUENCIA-PRODUCAO'];
        });
        $i = 0;
        $ultimos_artigos = [];
            foreach($artigos as $val){
                if($i > 4) break; $i++; 
                array_push($ultimos_artigos,$val['DADOS-BASICOS-DO-ARTIGO']['@attributes']['TITULO-DO-ARTIGO'] . 
                ' - Título do periódico ou revista: ' . $val['DETALHAMENTO-DO-ARTIGO']['@attributes']['TITULO-DO-PERIODICO-OU-REVISTA'] .
                ' - Volume: ' . $val['DETALHAMENTO-DO-ARTIGO']['@attributes']['VOLUME'] . 
                ' - Páginas: ' . $val['DETALHAMENTO-DO-ARTIGO']['@attributes']['PAGINA-INICIAL'] . '-'. $val['DETALHAMENTO-DO-ARTIGO']['@attributes']['PAGINA-FINAL'] .
                ' - Ano: ' . $val['DADOS-BASICOS-DO-ARTIGO']['@attributes']['ANO-DO-ARTIGO']);
            }
        return $ultimos_artigos;
        } else return false;
    }
}