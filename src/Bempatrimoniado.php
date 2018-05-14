<?php

namespace Uspdev\Replicado;

class Bempatrimoniado 
{

    private static informatica = [12513,51110,354384,354341,162213,9300,45624,57100];

    public static function dump($numpat)
    {
        $cols = file_get_contents('replicado_queries/tables/bempatrimoniado.sql', true);
        $query = " SELECT {$cols} FROM BEMPATRIMONIADO WHERE numpat = '{$numpar}'"; 
        $result = DB::fetch($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    public static function verifica($numpat)
    {
        $result = $this->dump($numpat);
        if (isset($result) && $result['stabem'] == 'Ativo') {
            return true;
        }
        else {
            return false;
        }
    }

    public static function ativos()
    {
        $cols = file_get_contents('replicado_queries/tables/bempatrimoniado.sql', true);
        $query = " SELECT {$cols} FROM BEMPATRIMONIADO WHERE stabem = 'Ativo'"; 
        $result = DB::fetchAll($query);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    public static function isInformatica($numpat)
    {
        $result = $this->dump($numpat);
        if (isset($result) && in_array($result['coditmmat'],$this->bems_informatica)) {
            return true;
        }
        else {
            return false;
        }
    }
    public static function ativosInformatica(){
    /*
     WHERE (BEMPATRIMONIADO.coditmmat = '12513'  OR 
            BEMPATRIMONIADO.coditmmat = '51110'  OR 
            BEMPATRIMONIADO.coditmmat = '354384' OR 
            BEMPATRIMONIADO.coditmmat = '354341' OR 
            BEMPATRIMONIADO.coditmmat = '9300' OR 
            BEMPATRIMONIADO.coditmmat = '162213' OR 
            BEMPATRIMONIADO.coditmmat = '57100' OR 
            BEMPATRIMONIADO.coditmmat = '45624')
     */    
    }
}
