SELECT 
    c.codcvn,
    c.nomcvn AS nomeConvenio,
    c.dtaasicvn AS dataInicio,
    c.dtadtvcvn AS dataFim
FROM CONVENIO c
JOIN CONVUNIDDESP u ON u.codcvn = c.codcvn
WHERE 
    c.tipcvn = 13 --convenio
    AND c.sticvn = 2 --internacional
    AND c.stacvn = 'Aprovado'
    AND u.codunddsp in (__codundclg__)
    AND c.dtaasicvn IS NOT NULL
    AND c.dtadtvcvn IS NOT NULL
    AND GETDATE() BETWEEN c.dtaasicvn AND c.dtadtvcvn
ORDER BY c.dtaasicvn;

