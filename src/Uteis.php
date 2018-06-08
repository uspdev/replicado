<?php

namespace Uspdev\Replicado;

class Uteis
{
    public static function removeAcentos($str) 
    {
        $map = [
            'á' => 'a',
            'à' => 'a',
            'ã' => 'a',
            'â' => 'a',
            'é' => 'e',
            'ê' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ç' => 'c',
            'Á' => 'A',
            'À' => 'A',
            'Ã' => 'A',
            'Â' => 'A',
            'É' => 'E',
            'Ê' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ú' => 'U',
            'Ü' => 'U',
            'Ç' => 'C'
        ];
        return strtr($str, $map);
    }

    public static function utf8_converter($array)
    {
        array_walk_recursive($array, function(&$item, $key){
            // fix ISO-8859-1 ?
            if(!mb_detect_encoding($item, 'utf-8', true)){
                $item = utf8_encode($item);
            }
        });
        return $array;
    }

    public static function trim_recursivo($array)
    {
        array_walk_recursive($array, function(&$item, $key){
            $item = trim($item);
        });
        return $array;
    }

    public static function makeCsv($array,$cols=null)
    {
        $csv = '';
        $csvKeys = '';

        foreach(array_keys($array[0]) as $key) {
            if(is_null($cols)){
                $csvKeys .= "$key,";
            }
            else {
                if (in_array($key,$cols,true)) $csvKeys .= "$key,"; 
            }
        }

        $csv .= rtrim($csvKeys, ',') . "\r\n";

        foreach($array as $row) {
            $line = '';

            foreach($row as $key => $value) {
                if(is_null($cols)){
                    $line .= "$value,";
                }
                else {
                    if (in_array($key,$cols,true)) $line .= "$value,";
                }
            }

            $line = rtrim($line, ',') . "\r\n";
            $csv .= $line;
        }

        return $csv;
    }




    /*
     * As funções abaixo são utilizadas para o fonetico
     * as regras utilizadas são as do sql e aplicadas na ordem sequencial, com algumas alterações ajustadas empiricamente
     * as exceções às regras estão documentadas na função
     * errou 200 numa amostra de 15K
     */

    public static function fonetico($str)
    {
        $log = false;
        $fon = ' '.trim(mb_strtoupper($str)).' '; // vamos colocar espaços para poder identificar o início e o fim (diferente do sql)

        $fon = Uteis::remove_accent($fon);
        $fon = Uteis::remove_especiais($fon); // remove o apostrofe dos nomes
        //echo $fon.PHP_EOL;
        $fon = Uteis::remove_prep($fon);
        //echo $fon.PHP_EOL;
        $fon = Uteis::elimina_repetidas($fon); // foi colocado aqui, talvez possa tirar do final
        if ($log) echo 'rep '.$fon.PHP_EOL;
        $fon = Uteis::trata_inicio_palavras($fon);
        if ($log) echo 'ini '.$fon.PHP_EOL;
        $fon = Uteis::trata_fim_palavras($fon);
        if ($log) echo 'fim '.$fon.PHP_EOL;
        $fon = Uteis::trata_meio_palavras($fon); // o tratamento do meio talvez tenha de ser em duas passagens pois pelo menos um caso precisou
        if ($log) echo 'me1 '.$fon.PHP_EOL;
        $fon = Uteis::trata_meio_palavras($fon);
        if ($log) echo 'me2 '.$fon.PHP_EOL;
        $fon = Uteis::trata_ln_consoante($fon);
        if ($log) echo 'ln  '.$fon.PHP_EOL;
        $fon = Uteis::troca_fonemas($fon);
        if ($log) echo 'fon '.$fon.PHP_EOL;
        $fon = Uteis::elimina_repetidas($fon);

        $fon = trim($fon);

        return $fon;
    }

    protected static function remove_especiais ($str)
    {
        $a = array('\'');
        $b = array('');
        return str_replace($a, $b, $str);
    }

    protected static function remove_accent($str)
    {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'S', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        return str_replace($a, $b, $str);
    }

    protected static function remove_prep($str)
    {
        $a = array(' E ', ' DA ', ' DE ', ' DI ', ' DO ', ' DU ', ' DAS ', ' DOS ');
        $b = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ');
        return str_replace($a, $b, $str);
    }

    protected static function trata_inicio_palavras($str)
    {
        $a = array(' WA', ' WE', ' WO', ' WU', ' WI', ' SQU', ' SQ', ' W');
        $b = array(' VA', ' VE', ' VO', ' VU', ' UI', ' ISQ', ' ISQ', ' ');
        return str_replace($a, $b, $str);
    }

    protected static function trata_fim_palavras($str)
    {
        // Exceções
        //AO->N:AN
        //AM->N:AN
        //L->O (novo)
        $a = array('OES ', 'ONS ', 'OIM ', 'UIM ', 'EIA ', 'AM ', 'AO ', '   OM ', 'TH ', 'N ', 'X ',  'D ', 'B ', 'T ', 'L ');
        $b = array('N ',   'N ',   ' N ',  'N ',   'IA ',  'AN ', 'AN ', '   N ',  'TE ', 'M ', 'IS ', ' ',  ' ',  ' ',  'O ');
        return str_replace($a, $b, $str);
    }

    protected static function trata_meio_palavras($str)
    {
        // TS->X:S
        $a = array('GN', 'MN', 'TSCH', 'TCH', 'SCH', 'TSH', 'SH', 'CH', 'LH', 'NH', 'PH',  'H', 'SCE', 'SCI', 'SCY', 'CS', 'KS', 'PS', 'TS', 'TZ', 'XS', 'CE', 'CI', 'CY', 'GE', 'GI', 'GY', 'GD', 'CK', 'PC', 'QU', 'SC', 'SK', 'XC', 'CT', 'GT', 'PT');
        $b = array('N',  'N',  'X',    'X',   'X',   'X',   'X',  'X',  'LI', 'N',  'F',   '',  'SE',  'SI' ,  'SY', 'X',  'X',  'X',  'S',  'X',  'X',  'SE', 'SI', 'SY', 'JE', 'JI', 'JY', 'D',  'Q',  'Q',  'Q',  'SQ', 'SQ', 'SQ', 'T',  'T',  'T');
        return str_replace($a, $b, $str);
    }

    protected static function trata_ln_consoante($str)
    {
        // LL nao troca
        // talvez tenha de refinar melhor o dicionario
        $str = preg_replace('/(L)(?=[BCDFGHJKMNPQRSTVXWZ])/', 'U', $str);
        return preg_replace('/(N)(?=[BCDFGHJKLMNPQRSTVXWZ])/', 'M', $str);
    }

    protected static function troca_fonemas($str)
    {
        $a = array('B', 'K', 'Q', 'T', 'E', 'Y', 'V', 'W', 'U', 'Z');
        $b = array('P', 'C', 'C', 'D', 'I', 'I', 'F', 'F', 'O', 'S');
        return str_replace($a, $b, $str);
    }

    protected static function elimina_repetidas($str)
    {
        $pattern = '/(.)\1+/';
        $replace = '$1';
        return preg_replace($pattern, $replace, $str);
    }
}

