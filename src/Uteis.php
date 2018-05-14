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
}

