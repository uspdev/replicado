<?php

namespace Uspdev\Replicado;

class Convenio
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
    public static function listarConveniosAcademicosInternacionais($ativos = true)
    {
        if ($ativos){
            $query = DB::getQuery('Convenios.listarConveniosAcademicosInternacionais.sql');
        }
        
        $convenios = DB::fetchAll($query);    
        
        // relacionamentos 1-N
        foreach ($convenios as $key=>$conv){
            
            // obtém responsáveis pelo convenio
            $query = DB::getQuery('Convenios.listarResponsaveisConvenio.sql');
            $params = [
                'codcvn' => $conv['codcvn']
            ];
            $resps = DB::fetchAll($query,$params);
            // armazenas os diversos responsaveis como uma string
            $convenios[$key]['responsaveis'] = '';
            foreach ($resps as $resp){               
                $convenios[$key]['responsaveis'].= $convenios[$key]['responsaveis']=='' ? Pessoa::nomeCompleto($resp['codpes']) : '|' . Pessoa::nomeCompleto($resp['codpes']);
            } 
            
            // obtém organizações envolvidas
            $query = DB::getQuery('Convenios.listarOrganizacoesConvenio.sql');
            $orgs = DB::fetchAll($query,$params);
            // armazena as diversas organizacoes como uma string
            $convenios[$key]['organizacoes'] = '';
            foreach ($orgs as $org){
                $convenios[$key]['organizacoes'].= $convenios[$key]['organizacoes']=='' ? $org['nomeOrganizacao'] : '|' . $org['nomeOrganizacao'];
            }
            
        }
           
        return $convenios;
        
    }

}
