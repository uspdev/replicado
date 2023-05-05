<?php

namespace Uspdev\Replicado;

class Pesquisa
{

    /**
     * Método para retornar as iniciações científicas
     * Permite filtrar por departamento e por periodo.
     * @param array $departamento - Recebe um array com as siglas dos departamentos desejados. Se for igual a null, a consulta trazerá todos os departamentos.
     * @param int $ano_ini - ano inicial do período. Se for igual a null retorna todas as iniciações científicas.
     * @param int $ano_fim - ano final do período
     * @param bool $somenteAtivos - Se for igual a true retornará as iniciações científicas ativas
     * @return array
     */
    public static function listarIniciacaoCientifica($departamento = null, $ano_ini = null, $ano_fim = null, $somenteAtivos = false)
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = DB::getQuery('Pesquisa.listarIniciacaoCientifica.sql');
        $query = str_replace('__unidades__', $unidades, $query);

        $param = [];

        if ($departamento != null && sizeof($departamento) > 0) {
            if (is_array($departamento) && sizeof($departamento) > 1) {
                $departamento = "'" . implode("','", $departamento) . "'";
            } else if (sizeof($departamento) == 1) {
                $departamento = "'" . $departamento[0] . "'";
            }

            $query = str_replace('__departamento__', "AND s.nomabvset in ($departamento)", $query);

        } else {
            $query = str_replace('__departamento__', "", $query);
        }
        if ($ano_ini != -1 && $ano_ini != null && $ano_fim != null && !empty($ano_ini) && !empty($ano_fim)) {
            $aux = " AND (ic.dtafimprj BETWEEN '" . $ano_ini . "-01-01' AND '" . $ano_fim . "-12-31' OR
                        ic.dtainiprj BETWEEN '" . $ano_ini . "-01-01' AND '" . $ano_fim . "-12-31') ";
            if ($somenteAtivos) {
                $aux .= " AND (ic.dtafimprj > GETDATE() or ic.dtafimprj IS NULL)";
            }
            $query = str_replace('__data__', $aux, $query);
        } else if ($ano_ini == null && !$somenteAtivos) {
            $query = str_replace('__data__', '', $query);
        } else if ($somenteAtivos) {
            $query = str_replace('__data__', "AND (ic.dtafimprj > GETDATE() or ic.dtafimprj IS NULL)", $query);
        }

        $result = DB::fetchAll($query, $param);

        $iniciacao_cientifica = [];
        foreach ($result as $ic) {
            $curso = Pessoa::retornarCursoPorCodpes($ic['aluno']);
            $ic['codcur'] = $curso == null ? null : $curso['codcurgrd'];
            $ic['nome_curso'] = $curso == null ? null : $curso['nomcur'];

            $query_com_bolsa = DB::getQuery('Pesquisa.buscarICcomBolsaPorCodpes.sql');
            $query_com_bolsa = str_replace('__unidades__', $unidades, $query_com_bolsa);

            $param_com_bolsa = [
                'codpes' => $ic['aluno'],
                'codprj' => $ic['cod_projeto'],
            ];
            $result = DB::fetchAll($query_com_bolsa, $param_com_bolsa);
            if (count($result) == 0) {
                $ic['bolsa'] = 'false';
                $ic['codctgedi'] = '';
            } else {
                $ic['bolsa'] = 'true';
                $ic['codctgedi'] = $result[0]['codctgedi'] == '1' ? 'PIBIC' : 'PIBITI';
            }

            array_push($iniciacao_cientifica, $ic);
        }
        return $iniciacao_cientifica;
    }

    /**
     * Método para retornar os colaboradores ativos
     *
     * @return array
     */
    public static function listarPesquisadoresColaboradoresAtivos()
    {
        $query = DB::getQuery('Pesquisa.listarPesquisadoresColaboradoresAtivos.sql');

        //TODO fazer o filtro por unidade
        //$unidades = getenv('REPLICADO_CODUNDCLG');
        //$query = str_replace('__unidades__',$unidades,$query);

        return DB::fetchAll($query);
    }

    /**
     * Método para listar os pós-doutorandos e dados do projeto, supervisor e setor
     *
     * Ajustado para versão 1.16
     * Testado em php 8.2 e 7.4
     *
     * @return array
     * @author Masakik, em 4/5/2023, ajustes para versão 1.16 #537
     */
    public static function listarPesquisaPosDoutorandos()
    {
        $pesquisas_pos_doutorando = [];
        $query = DB::getQuery('Pesquisa.listarPesquisaPosDoutorandos.sql');
        $pesquisas = DB::fetchAll($query);

        foreach ($pesquisas as $p) {
            $query_nome_supervisor = DB::getQuery('Pessoa.retornarSupervisorPesquisaPosDoutorando.sql');
            $query_nome_supervisor = str_replace(' __codprj__', "codprj = convert(int,:codprj)", $query_nome_supervisor);
            $supervisor = DB::fetchAll($query_nome_supervisor, ['codprj' => $p['codprj']]);
            $p['supervisor'] = $supervisor[0]['nompes'];

            $query_com_bolsa = DB::getQuery('Pesquisa.buscarPDcomBolsaPorCodpes.sql');
            $result = DB::fetchAll($query_com_bolsa, ['codpes' => $p['codpes']]);
            $p['bolsa'] = (count($result) == 0) ? 'false' : 'true';
            
            array_push($pesquisas_pos_doutorando, $p);
        }

        return $pesquisas_pos_doutorando;
    }

}
