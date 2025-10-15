SELECT 
    c.codcvn,
    c.nomcvn AS nomeConvenio,
    CONVERT(VARCHAR(10), c.dtaasicvn, 103) AS dataInicio,
    CONVERT(VARCHAR(10), c.dtadtvcvn, 103) AS dataFim
FROM CONVENIO c
JOIN CONVUNIDDESP u ON u.codcvn = c.codcvn
WHERE 
    c.tipcvn = 13
    AND c.sticvn = 2
    AND c.stacvn = 'Aprovado'
    AND u.codunddsp IN (__codundclgs__)
    AND c.dtaasicvn IS NOT NULL
    AND c.dtadtvcvn IS NOT NULL
    AND c.dtadtvcvn < GETDATE()
ORDER BY c.dtaasicvn;
