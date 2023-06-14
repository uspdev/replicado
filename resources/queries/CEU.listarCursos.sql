SELECT  e.codcurceu, e.codedicurceu, c.nomcurceu,  
        c.codsetdep, s.nomset, 
        cast(c.objcur as NVARCHAR(MAX)) as objcur ,cast(c.juscur as NVARCHAR(MAX)) as juscur,  
        c.fmtcurceu, c.dscpbcinr, e.dtainiofeedi,  
        e.dtafimofeedi, e.qtdvagofe, f.numpro, f.staedi, count(m.codpes) as matriculados 
FROM EDICAOCURSOOFECEU e 
LEFT JOIN CURSOCEU c ON c.codcurceu = e.codcurceu   
LEFT JOIN SETOR s ON c.codsetdep = s.codset 
LEFT JOIN EDICAOCURSOCEU f ON (f.codcurceu = c.codcurceu AND f.codedicurceu = e.codedicurceu) 
LEFT JOIN MATRICULACURSOCEU m ON (m.codcurceu = c.codcurceu AND m.codedicurceu = e.codedicurceu) 
WHERE  
 c.codclg in (__codundclgs__) 
__deptos__
    AND  
        (year(e.dtainiofeedi) BETWEEN convert(int,:ano_inicio) AND convert(int,:ano_fim) 
        OR  
        (year(e.dtafimofeedi) BETWEEN convert(int,:ano_inicio) AND convert(int,:ano_fim))) 
group by e.codcurceu, e.codedicurceu, c.nomcurceu,  
        c.codsetdep, s.nomset,
        cast(c.objcur as NVARCHAR(MAX)) ,cast(c.juscur as NVARCHAR(MAX)),  
        c.fmtcurceu, c.dscpbcinr, e.dtainiofeedi,  
        e.dtafimofeedi, e.qtdvagofe, f.numpro, f.staedi
ORDER BY  
    e.dtainiofeedi

-- o group by com quase todos os itens do select é para funcionar o 'count(m.codpes) as matriculados'