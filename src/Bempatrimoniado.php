<?php

namespace Uspdev\Replicado;

class Bempatrimoniado
{

    private static $BEM_INFORMATICAS = [12513,51110,354384,354341,162213,9300,45624,57100];

    public static function dump(string $numpat, array $fields = ['*'])
    {
        $numpat = str_replace('.', '', $numpat);
        $columns = implode(",",$fields);

        $query = " SELECT {$columns} FROM BEMPATRIMONIADO WHERE numpat = convert(decimal,:numpat)";
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
        $result = Bempatrimoniado::dump($numpat, ['stabem']);
        if (isset($result) && $result['stabem'] == 'Ativo') {
            return true;
        }
        return false;
    }

    /**
     * Retorna todos bens patrimoniados ativos (com opção de filtro)
     *  
     * @param array $params (opcional) - campo_tabela => valor
     * @param string $operador (default = AND) - operador da cláusula WHERE
     * 
     * @return array Retorna todos os campos da tabela BEMPATRIMONIADO
     */
    public static function ativos($params = null, $operador = 'AND')
    {
        if (is_null($params)) {
            $query = " SELECT * FROM BEMPATRIMONIADO WHERE stabem = 'Ativo'";
        } else {
            $query = " SELECT * FROM BEMPATRIMONIADO WHERE stabem = 'Ativo' AND (";

            foreach ($params as $campo => $valor) {
                if (gettype($valor) == 'integer') {
                    $query .= " {$campo} = {$valor} ";
                } else if (gettype($valor) == 'string') {
                    $valor = Uteis::removeAcentos($valor);
                    $query .= " {$campo} LIKE '%{$valor}%' COLLATE Latin1_General_CI_AI ";
                }

                // Enquanto houver item no array, adiciona AND/OR na clásula WHERE
                if (next($params)) {
                    $query .= $operador;
                } else {
                    $query .= ")";
                }
            }
        }

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
