<?php

namespace Uspdev\Replicado;

class CEU
{
    /**
     * Método para retornar os cursos de cultura e extensão de um período, permite filtrar por departamentos também
     *
     * Não traz cursos cancelados (ec.staedi != 'CAN')
     * Traz cursos mesmo que o nro de matriculados seja 0
     *
     * @param int $ano_inicio - ano inicial do período (se null, usa o ano atual)
     * @param int $ano_fim - ano final do período (se null, usa igual $ano_inicio ano atual)
     * @param array $deptos - Recebe um array ou valores separados por vírgula com os códigos dos departamentos desejados. Se for igual a null, a consulta retornará todos os departamentos.
     * @return array
     * @author Erickson Zanon <ezanon@gmail.com> alterado em 06/2023 #545
     */
    public static function listarCursos($ano_inicio = null, $ano_fim = null, $deptos = null)
    {

        // se não foi enviado $ano_inicio, atribui o ano atual
        $ano_inicio = $ano_inicio ?: date('Y');
        // se não foi enviado $ano_fim, atribui mesmo valor do $ano_inicio
        $ano_fim = $ano_fim ?: $ano_inicio;

        $query = DB::getQuery('CEU.listarCursos.sql');

        // adiciona os departamentos
        $query_deptos = '';
        if ($deptos != null) {
            if (!is_array($deptos)) { // enviado valores separados por vírgula ou um único valor
                $query_deptos = "AND C.codsetdep IN ($deptos)";
            } else { // enviado array
                $query_deptos = implode(',', $deptos);
                $query_deptos = "AND C.codsetdep IN ($query_deptos)";
            }
        }
        $query = str_replace('__deptos__', $query_deptos, $query);

        // executando a query
        $params = [
            'ano_inicio' => $ano_inicio,
            'ano_fim' => $ano_fim,
        ];
        $result_cursos = DB::fetchAll($query, $params);

        // recuperar mais informações dos cursos obtidos
        $cursos = [];
        foreach ($result_cursos as $curso) {
            // obter ministrantes
            $query = DB::getQuery('CEU.listarMinistrantesPorCurso.sql');
            $params = [
                'codcurceu' => $curso['codcurceu'],
                'codedicurceu' => $curso['codedicurceu'],
            ];
            $result_ministrantes = DB::fetchAll($query, $params);
            $curso['ministrantes'] = implode(', ', array_column($result_ministrantes, 'nompes'));

            // adiciona o curso ao array cursos que será retornado
            array_push($cursos, $curso);
        }
        return $cursos;
    }

}
