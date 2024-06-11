<?php

namespace Uspdev\Replicado;

class Estrutura
{
    /**
     * Método que recebe codset e retorna todos campos da tabela SETOR.
     *
     * @param Integer $codset - Código do setor
     * @return string
     * @author André Canale Garcia <acgarcia@sc.sp.br>
     */
    public static function dump($codset)
    {        
        $query = "SELECT s.*
                  FROM SETOR AS s 
                  WHERE s.codset = convert(int,:codset)";

        $param = [
            'codset' => $codset,
        ];

        return DB::fetch($query, $param);
    }

     /**
     * Método que recebe o Código da Unidade e retorna todos os setores ativos da mesma.
     * Caso não seja passado a unidade, pega o REPLICADO_CODUNDCLG do .env
     * 
     * @param Integer $codund - código da Unidade
     * @return array
     * @author Fernando G. Moura <fgm@sc.sp.br>
     */
    public static function listarSetores($codund = null)
    {
        $query = "SELECT codset, tipset, nomabvset, nomset, codsetspe  FROM SETOR                   
                  WHERE codund = convert(int,:codund) AND dtadtvset IS NULL AND nomset NOT LIKE 'Inativo'
                  ORDER BY codset ASC";
        
        if ($codund) {
            $param = [
                'codund' => $codund,
            ];
        }else{
            $unidades = getenv('REPLICADO_CODUNDCLG');
            $param = [
                'codund' => $unidades,
            ];
        } 

        return DB::fetchAll($query, $param);
    }

    /**
     * Método que recebe o cógido do setor para retornar a(s) chefia(s) do mesmo
     *
     * @param Integer $codset - Código do setor
     * @param boolean $substitutos - true (inclui todas as designações), false (exclui as temporárias)
     * @return array
     * @author Fernando G. Moura <fgm@sc.sp.br>
     */
    public static function getChefiaSetor($codset, $substitutos = true)
    {        
        if ($substitutos) { 
            //substituição de uma designação já existente (S); exercício de liderança em substituição (E);
            $s = ''; 
        }else{ 
            //designação uma função (D); pró-labore (P); exercício de liderança (L); Exercendo Coordenação (C).
            $s = "AND c.tipdsg LIKE 'D' OR c.tipdsg LIKE 'P' OR c.tipdsg LIKE 'L' OR c.tipdsg LIKE 'C'";
        }
        
        $query = "SELECT c.codpes, c.nompes, c.nomfnc, s.codsetspe, s.nomabvset, s.nomset  FROM SETOR AS s 
            INNER JOIN LOCALIZAPESSOA AS c
            ON c.codset = s.codset
            WHERE s.codset = convert(int,:codset) AND s.dtadtvset IS NULL AND c.tipvinext LIKE 'Servidor Designado' " . $s . "
            ORDER BY s.tipset ASC, s.nomset ASC";

        $param = [
            'codset' => $codset,
        ];

        return DB::fetchAll($query, $param);
    }

    /**
     * Retorna lista com todas as unidades ativas da universidade.
     * 
     * @return Array
     * @author Kawan Santana, em 19/03/2024
     */
    public static function listarUnidades(){
        $query = DB::getQuery('Estrutura.listarUnidades.sql');
        return Db::fetchAll($query);
    }

    /**
     * Método que retorna todos campos da tabela UNIDADE.
     * Fetch retornando apenas um registro, logo somente um código de unidade
     *  
     * @param Integer $codund - Código da unidade 
     * @return Array
     * @author Alessandro Costa de Oliveira, em 11/06/2024
     */
    public static function obterUnidade($codund)
    {        
        $query = DB::getQuery('Estrutura.obterUnidade.sql');
        $param = ['codund' => $codund];
        return DB::fetch($query, $param);
    }

}
