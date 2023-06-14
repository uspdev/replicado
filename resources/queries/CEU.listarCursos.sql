SELECT
    e.codcurceu, e.codedicurceu,
    c.nomcurceu, cast(c.objcur as NVARCHAR(MAX)) as objcur, cast(c.juscur as NVARCHAR(MAX)) as juscur, c.dscpbcinr, c.fmtcurceu,
    s.codset, s.nomset, s.nomabvset,
    ec.numpro, ec.staedi,
    convert(varchar, e.dtainiofeedi, 103) as dtainiofeedi, convert(varchar, e.dtafimofeedi, 103) as dtafimofeedi, e.qtdvagofe,
    count(m.codpes) as matriculados
FROM EDICAOCURSOOFECEU e
LEFT JOIN CURSOCEU c ON c.codcurceu = e.codcurceu
LEFT JOIN SETOR s ON c.codsetdep = s.codset
LEFT JOIN EDICAOCURSOCEU ec ON (ec.codcurceu = c.codcurceu AND ec.codedicurceu = e.codedicurceu)
LEFT JOIN MATRICULACURSOCEU m ON (m.codcurceu = c.codcurceu AND m.codedicurceu = e.codedicurceu)
WHERE
    c.codclg in (__codundclgs__)
    __deptos__
    AND
        ec.staedi != 'CAN' -- edição do curso cancelada
    AND (
        (year(e.dtainiofeedi) BETWEEN convert(int,:ano_inicio) AND convert(int,:ano_fim))
        OR (year(e.dtafimofeedi) BETWEEN convert(int,:ano_inicio) AND convert(int,:ano_fim))
    )
GROUP BY -- o group by com quase todos os itens do select é para funcionar o 'count(m.codpes) as matriculados'
    e.codcurceu, e.codedicurceu,
    c.nomcurceu, cast(c.objcur as NVARCHAR(MAX)), cast(c.juscur as NVARCHAR(MAX)), c.dscpbcinr, c.fmtcurceu,
    s.codset, s.nomset, s.nomabvset,
    ec.numpro, ec.staedi,
    e.dtainiofeedi, e.dtafimofeedi, e.qtdvagofe
ORDER BY
    e.dtainiofeedi
