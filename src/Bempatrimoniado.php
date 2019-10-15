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

    /**
     * Retorna todos bens patrimoniados ativos (com opção de filtros e buscas)
     *  
     * @param array $filtros (default) - stabem => 'Ativo'
     * @param array $buscas (opcional) - campo_tabela => valor
     * @param array $tipos (opcional) - campo_tabela => tipo (ex.: codpes => int)
     * 
     * @return array Retorna todos os campos da tabela BEMPATRIMONIADO
     */
    public static function ativos($filtros = [], $buscas = [], $tipos = [])
    {
        $filtros['stabem'] = 'Ativo';
        $result = self::bens($filtros, $buscas, $tipos);
        
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

    /**
     * Retorna todos bens patrimoniados (com opção de filtros e buscas)
     *  
     * @param array $filtros (opcional) - campo_tabela => valor
     * @param array $buscas (opcional) - campo_tabela => valor
     * @param array $tipos (opcional) - campo_tabela => tipo (ex.: codpes => int)
     * 
     * @return array Retorna todos os campos da tabela BEMPATRIMONIADO
     */
    public static function bens($filtros = [], $buscas = [], $tipos = [])
    {
        $query = " SELECT * FROM BEMPATRIMONIADO ";
        $filtros_buscas = self::criaFiltroBusca($filtros, $buscas, $tipos);
        // Atualiza a cláusula WHERE do sql
        $query .= $filtros_buscas[0];
        // Define os parâmetros para cláusula WHERE
        $params = $filtros_buscas[1];
    
        $result = DB::fetchAll($query, $params);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }

    public static function criaFiltroBusca($filtros, $buscas, $tipos)
    {
        // Abre o parênteses dos filtros
        $str_where = " WHERE (";
        $params = [];
        if (!empty($filtros) && (count($filtros) > 0)) {
            foreach ($filtros as $coluna => $valor) {
                if (array_key_exists($coluna, $tipos)) {
                    $str_where .= " {$coluna} = convert({$tipos[$coluna]}, :{$coluna}) ";
                    $params[$coluna] = "{$valor}";
                } else {
                    $str_where .= " {$coluna} = :{$coluna} ";
                    $params[$coluna] = "{$valor}";
                }
                // Enquanto existir um filtro, adiciona o operador AND
                if (next($filtros)) {
                    $str_where .= ' AND ';
                }
            }
        }

        if (!empty($buscas) && (count($buscas) > 0)) {
            // Caso exista um campo para busca, fecha os parênteses anterior
            // e adiciona mais um AND (, que conterá os parâmetros de busca (OR)
            $str_where .= ') AND (';
            foreach ($buscas as $coluna => $valor) {
                $str_where .= " {$coluna} LIKE :{$coluna} ";
                $params[$coluna] = "%{$valor}%";

                // Enquanto existir uma busca, adiciona o operador OR
                if (next($buscas)) {
                    $str_where .= ' OR ';
                } else {
                    // Fecha o parênteses do OR
                    $str_where .= ') ';
                }
            }
        } else {
            // Fecha o parênteses dos filtros
            $str_where .= ')';
        }

        return [$str_where, $params];
    }
}

