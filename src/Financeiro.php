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
                    WHERE dtadtv IS NULL";
        
        if ($codund) {
            $query .= " AND codunddsp = convert(int,:codund)";
            $param = [
                'codund' => $codund,
            ];
            $result = DB::fetchAll($query, $param);
        }else{
            $unidades = explode(",",getenv('REPLICADO_CODUNDCLG'));
            $i = 0;
            foreach($unidades as $unidade){
                if($i == 0){
                    $query .= " AND codunddsp = $unidade";
                }
                else{
                    $query .= " OR codunddsp = $unidade";
                }
                $i++;
            }
            $result = DB::fetchAll($query);
        }      
        return $result;
    }

}
