<?php

namespace Uspdev\Replicado;

class Convenio {

    /**
     * Método para listar convênios acadêmicos internacionais.
     *
     * Quando o parâmetro $ativos for verdadeiro, carrega apenas convênios ativos,
     * conforme definido na consulta 'Convenios.listarConveniosAcademicosInternacionais.sql'.
     * Além dos dados principais, o método também agrega as informações de responsáveis e
     * organizações associadas a cada convênio, unificando-os em strings separadas por '|'.
     *
     * @param bool $ativos - Define se devem ser listados apenas convênios ativos (true) ou todos (false).
     * @return array - Retorna um array associativo contendo os convênios, seus responsáveis e organizações.
     * @author Erickson Zanon <ezanon@gmail.com>
     */
    public static function listarConveniosAcademicosInternacionais($ativos = true) {
        if ($ativos) {
            $query = DB::getQuery('Convenios.listarConveniosAcademicosInternacionais.sql');
        }
        else {
            $query = DB::getQuery('Convenios.listarConveniosAcademicosInternacionaisInativos.sql');
        }

        $convenios = DB::fetchAll($query);

        // relacionamentos 1-N
        foreach ($convenios as $key => $conv) {

            $codcvn = $conv['codcvn'];

            // obtém responsáveis pelo convênio
            $resps = self::listarResponsaveisConvenio($codcvn);

            // armazena os diversos responsáveis como uma string separada por '|'
            $convenios[$key]['responsaveis'] = '';
            foreach ($resps as $resp) {
                $convenios[$key]['responsaveis'] .= $convenios[$key]['responsaveis'] == '' ? Pessoa::nomeCompleto($resp['codpes']) : '|' . Pessoa::nomeCompleto($resp['codpes']);
            }

            // obtém organizações envolvidas
            $orgs = self::listarOrganizacoesConvenio($codcvn);

            // armazena as diversas organizações como uma string separada por '|'
            $convenios[$key]['organizacoes'] = '';
            foreach ($orgs as $org) {
                $convenios[$key]['organizacoes'] .= $convenios[$key]['organizacoes'] == '' ? $org['nomeOrganizacao'] : '|' . $org['nomeOrganizacao'];
            }
        }

        return $convenios;
    }

    /**
     * Método para listar os responsáveis vinculados a um convênio específico.
     *
     * Utiliza a consulta 'Convenios.listarResponsaveisConvenio.sql' para obter os registros
     * de responsáveis associados ao código do convênio informado.
     *
     * @param int $codcvn - Código do convênio cujos responsáveis serão consultados.
     * @return array - Retorna um array associativo contendo os responsáveis do convênio.
     * @author Erickson Zanon <ezanon@gmail.com>
     */
    public static function listarResponsaveisConvenio($codcvn) {
        $query = DB::getQuery('Convenios.listarResponsaveisConvenio.sql');
        $params = [
            'codcvn' => $codcvn
        ];
        $responsaveis = DB::fetchAll($query, $params);

        return $responsaveis;
    }

    /**
     * Método para listar as organizações externas vinculadas a um convênio específico.
     *
     * Utiliza a consulta 'Convenios.listarOrganizacoesConvenio.sql' para obter as organizações
     * relacionadas ao convênio informado, conforme seu código.
     *
     * @param int $codcvn - Código do convênio cujas organizações serão consultadas.
     * @return array - Retorna um array associativo contendo as organizações do convênio.
     * @author Erickson Zanon <ezanon@gmail.com>
     */
    public static function listarOrganizacoesConvenio($codcvn) {
        $query = DB::getQuery('Convenios.listarOrganizacoesConvenio.sql');
        $params = [
            'codcvn' => $codcvn
        ];
        $organizacoes = DB::fetchAll($query, $params);

        return $organizacoes;
    }
}
