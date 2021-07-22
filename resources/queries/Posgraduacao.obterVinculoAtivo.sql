SELECT p.nompes as nompesori, r.codpes as codpesori,
    n.nomare,
    nc.nomcur,
    v.*
FROM VINCULOPESSOAUSP v
JOIN R39PGMORIDOC r ON (v.codpes = r.codpespgm AND r.dtafimort IS NULL) --obter codpes orientador
JOIN PESSOA p ON (p.codpes = r.codpes) --obter nome orientador
JOIN AREA a ON (a.codare = v.codare) --obter codcur
JOIN NOMEAREA n ON (v.codare = n.codare and n.dtafimare IS NULL) --obter nomeare
JOIN NOMECURSO nc on (a.codcur = nc.codcur AND nc.dtafimcur IS NULL) --obter nomecur
WHERE v.codpes = convert(int,:codpes)
AND v.tipvin IN ('ALUNOPOS','INSCRITOPOS') 
AND v.sitatl = 'A'