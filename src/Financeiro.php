<?php

namespace Uspdev\Replicado;

class Financeiro
{
     /**
     * Método que recebe o Código da Unidade e retorna os centros de despesas da mesma.
     * Caso não seja passado a unidade, pega o REPLICADO_CODUNDCLG do .env
     * 
     * @param Integer $codund - código da Unidade
     * @return array
     * @author Victor de O. Marinho <victor.oliveira.marinho@usp.br>
     */
    public static function listarCentrosDespesas($codund = null)
    {
        $query = "SELECT etrhie FROM CENTRODESPHIERARQUIA
                    WHERE dtadtv IS NULL
                    AND codunddsp = convert(int,:codund)";
        
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

}
