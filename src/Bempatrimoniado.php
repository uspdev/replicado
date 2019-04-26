<?php

namespace Uspdev\Replicado;

class Bempatrimoniado
{

    private static $BEM_INFORMATICAS = [12513,51110,354384,354341,162213,9300,45624,57100];

    public static function dump($numpat)
    {
        $query = " SELECT * FROM BEMPATRIMONIADO WHERE numpat = convert(decimal,:numpat)";
        $param = [
            'numpat' => $numpat,
        ];

        $result = DB::fetch($query, $param);
        if (!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }
        return false;
    }

    public static function verifica($numpat)
    {
        $result = Bempatrimoniado::dump($numpat);
        if (isset($result) && $result['stabem'] == 'Ativo') {
            return true;
        }
        
        return false;
    }

    public static function ativos()
    {
        $query = " SELECT * FROM BEMPATRIMONIADO WHERE stabem = 'Ativo'";
        $result = DB::fetchAll($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    public static function isInformatica($numpat)
    {
        $result = Bempatrimoniado::dump($numpat);
        if (isset($result) && in_array($result['coditmmat'],self::$BEM_INFORMATICAS)) {
            return true;
        } 
        return false;
    }
}
