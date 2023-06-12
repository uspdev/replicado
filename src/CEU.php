<?php

namespace Uspdev\Replicado;

class CEU
{
    
    /**
     * Método para retornar os cursos de cultura e extensão de um período, permite filtrar por departamentos também 
     * @param int $ano_inicio - ano inicial do período (se null, usa o ano atual)
     * @param int $ano_fim - ano final do período (se null, usa igual $ano_inicio ano atual)
     * @param array $deptos - Recebe um array com os códigos dos departamentos desejados. Se for igual a null, a consulta retornará todos os departamentos.
     * @return array
     */    
    public static function listarCursos($ano_inicio = null, $ano_fim = null, $deptos = null){
        
        // se não foi enviado $ano_inicio, atribui o ano atual
        if (!$ano_inicio) $ano_inicio = date('Y');
        // se não foi enviado $ano_fim, atribui mesmo valor do $ano_inicio
        if (!$ano_fim) $ano_fim = $ano_inicio;
        
        $unidades = getenv('REPLICADO_CODUNDCLG');   
            
        $query = DB::getQuery('CEU.listarCursos.sql');
        
        //adiciona as unidades
        $query = str_replace('__unidades__', $unidades, $query);
        
        // adiciona a parte dos anos
        $anos = " AND e.dtainiofeedi BETWEEN '".$ano_inicio."-01-01' AND '".$ano_fim."-12-31'";
        $query = str_replace('__anos__', $anos, $query);
        
        // adiciona os departamentos   
        $deptoo = '';
        if ($deptos != NULL){ 
            if (!is_array($deptos)){ // enviado valores separados por vírgula ou um único valor
                $deptoo = "AND C.codsetdep IN ($deptos)";
            }
            else { // enviado array
                $deptoo = implode(",", $deptos);
                $deptoo = "AND C.codsetdep IN ($deptoo)";
            }  
        }
        $query = str_replace('__deptos__',$deptoo, $query);  
        
        // executando a query
        $result = DB::fetchAll($query);
        
        // recuperar mais informações dos cursos obtidos
        $cursos = [];
        foreach($result as $curso){
            
            // contar matriculados
            $query = DB::getQuery('CEU.contarMatriculadosCurso.sql');
            $param = [
                'codcurceu' => $curso['codcurceu'],
                'codedicurceu' => $curso['codedicurceu']       
            ];
            $result_matriculados = DB::fetchAll($query, $param);
            $curso['matriculados'] = $result_matriculados[0]['matriculados'];
            
            // obter ministrantes
            $query = DB::getQuery('CEU.listarMinistrantesPorCurso.sql');
            $param = [
                'codcurceu' => $curso['codcurceu'],
                'codedicurceu' => $curso['codedicurceu']       
            ];
            $result_ministrantes = DB::fetchAll($query, $param);
            if (count($result_ministrantes) == 0){
                $curso['ministrantes'] = '-';
            }
            else{
                $ministrantes = [];
                foreach ($result_ministrantes as $m){
                    array_push($ministrantes, $m['nompes']);
                }
                $curso['ministrantes'] = implode(", ", $ministrantes);
            }
            
            // adiciona este curso ao array
            array_push($cursos, $curso);     
            
            unset($curso);
        }       
        return $cursos;
    }
    
}
