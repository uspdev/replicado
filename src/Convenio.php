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
    public static function listarConveniosAcademicosInternacionais($ativos = true)
    {
        // Define qual consulta usar
        if ($ativos) {
            $query = DB::getQuery('Convenio.listarConveniosAcademicosInternacionais.sql');
        } else {
            $query = DB::getQuery('Convenio.listarConveniosAcademicosInternacionaisInativos.sql');
        }

        $convenios = DB::fetchAll($query);

        // Processa relacionamentos e formata datas
        foreach ($convenios as $key => $conv) {

            $codcvn = $conv['codcvn'];

            // 🔹 Converte datas (mantendo compatibilidade MSSQL/Sybase)
            $inicio = !empty($conv['dataInicio']) ? date('d/m/Y', strtotime($conv['dataInicio'])) : '—';
            $fim = !empty($conv['dataFim']) ? date('d/m/Y', strtotime($conv['dataFim'])) : '—';
            $convenios[$key]['dataInicio'] = $inicio;
            $convenios[$key]['dataFim'] = $fim;

            // 🔹 Obtém responsáveis
            $resps = self::listarResponsaveisConvenio($codcvn);
            $convenios[$key]['responsaveis'] = '';
            foreach ($resps as $resp) {
                $nome = Pessoa::nomeCompleto($resp['codpes']);
                $convenios[$key]['responsaveis'] .= $convenios[$key]['responsaveis'] == '' ? $nome : '|' . $nome;
            }

            // 🔹 Obtém organizações
            $orgs = self::listarOrganizacoesConvenio($codcvn);
            $convenios[$key]['organizacoes'] = '';
            foreach ($orgs as $org) {
                $nomeOrg = $org['nomeOrganizacao'];
                $convenios[$key]['organizacoes'] .= $convenios[$key]['organizacoes'] == '' ? $nomeOrg : '|' . $nomeOrg;
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
        $query = DB::getQuery('Convenio.listarResponsaveisConvenio.sql');
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
        $query = DB::getQuery('Convenio.listarOrganizacoesConvenio.sql');
        $params = [
            'codcvn' => $codcvn
        ];
        $organizacoes = DB::fetchAll($query, $params);

        return $organizacoes;
    }
}
