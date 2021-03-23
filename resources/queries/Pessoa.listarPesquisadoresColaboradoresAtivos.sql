SELECT DISTINCT l.codpes, 
d.codprj, 
l.nompes AS pesquisador, 
p.titprj as titulo_pesquisa, 
n.nompes as responsavel, 
s.nomset as departamento,
s.nomabvset as sigla_departamento,
p.dtainiprj as data_ini,
p.dtafimprj as data_fim
FROM fflch.dbo.LOCALIZAPESSOA l
INNER JOIN fflch.dbo.PDPROJETO p ON l.codpes = p.codpes_pd 
inner join fflch.dbo.PDPROJETOSUPERVISOR d ON d.codprj = p.codprj
inner join fflch.dbo.PESSOA n ON n.codpes = d.codpesspv 
inner join fflch.dbo.VINCULOPESSOAUSP v on l.codpes = v.codpes
inner join fflch.dbo.SETOR s on p.codsetprj = s.codset 
where l.tipvin = 'PESQUISADORCOLAB' and d.dtainispv IS NOT NULL
AND p.staatlprj = 'Ativo'
ORDER BY l.nompes