<?php

namespace Uspdev\Replicado;

class CEU
{


    /**
     * Método para retornar os cursos de cultura e extensão de um período, permite filtrar por departamentos também 
     * @param array $departamento - Recebe um array com os códigos dos departamentos desejados. Se for igual a null, a consulta trazerá todos os departamentos.
     * @param int $ano_ini - ano inicial do período
     * @param int $ano_fim - ano final do período
     * @return array
     */
    public static function listarCursos($ano_inicio, $ano_fim, $departamento = null)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');   
            
        $query = DB::getQuery('CEU.listarCursos.sql');
        $query = str_replace('__unidades__',$unidades,$query);

        if($departamento != null && sizeof($departamento) > 0){ 
            if(is_array($departamento) && sizeof($departamento) > 1){
                $departamento = implode(",", $departamento);
            }else if(sizeof($departamento) == 1){
                $departamento = $departamento[0];
            } 
            $query = str_replace('__departamento__',"AND C.codsetdep IN ($departamento)", $query);
           
        }else{
            $query = str_replace('__departamento__',"", $query);
        }
               

        if($ano_inicio == $ano_fim){
            $query = str_replace('__ano__'," AND C.dtainc LIKE '%".$ano_fim."%'", $query);
        } else{
            $query = str_replace('__ano__'," AND C.dtainc BETWEEN '".$ano_inicio."-01-01' AND '".$ano_fim."-12-31'", $query);
        }
        
        $result = DB::fetchAll($query);

        $cursos = [];
        foreach($result as $curso){
            $query = DB::getQuery('CEU.listarMinistrantesPorCurso.sql');
            $param = [
                'codofeatvceu' => $curso['codofeatvceu']
            ];
            $result_ministrantes = DB::fetchAll($query, $param);
            if(count($result_ministrantes) == 0){
                $curso['ministrantes'] = '-';
            }else{
                $ministrantes = [];
                foreach($result_ministrantes as $m){
                    array_push($ministrantes, $m['nompes']);
                }
                $curso['ministrantes'] = implode(", ", $ministrantes);
            }
            unset($curso['codofeatvceu']);
            
            array_push($cursos, $curso);
        }
        return $cursos;

    }
    

}
