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
     * Retorna todos bens patrimoniados ativos (com opção de filtros e buscas)
     *  
     * @param array $filtros (default) - stabem => 'Ativo'
     * @param array $buscas (opcional) - campo_tabela => valor
     * @param array $tipos (opcional) - campo_tabela => tipo (ex.: codpes => int)
     * @param integer $limite (default) - 2000
     * 
     * @return array Retorna todos os campos da tabela BEMPATRIMONIADO
     */
    public static function ativos(array $filtros = [], array $buscas = [], array $tipos = [], int $limite = 2000)
    {
        $filtros['stabem'] = 'Ativo';
        $result = self::bens($filtros, $buscas, $tipos, $limite);
        
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
     * @param integer $limite (default) - 2000
     * 
     * @return array Retorna todos os campos da tabela BEMPATRIMONIADO
     */
    public static function bens(array $filtros = [], array $buscas = [], array $tipos = [], int $limite = 2000)
    {
        $query = " SELECT TOP {$limite} * FROM BEMPATRIMONIADO ";
        $filtros_buscas = DB::criaFiltroBusca($filtros, $buscas, $tipos);
        // Atualiza a cláusula WHERE do sql
        $query .= $filtros_buscas[0];
        // Define os parâmetros para cláusula WHERE
        $params = $filtros_buscas[1];
    
        $result = DB::fetchAll($query, $params);
        $result = Uteis::utf8_converter($result);
        $result = Uteis::trim_recursivo($result);
        return $result;
    }
}

