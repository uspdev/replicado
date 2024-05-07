SELECT D1.*
FROM DISCIPLINAGR AS D1
WHERE (D1.verdis = (SELECT MAX(D2.verdis) FROM DISCIPLINAGR AS D2 WHERE (D2.coddis = D1.coddis)))
AND D1.coddis IN (SELECT coddis FROM DISCIPGRCODIGO WHERE DISCIPGRCODIGO.codclg IN (__codundclgs__))
AND D1.dtadtvdis IS NULL -- nao foi desativado
AND D1.dtaatvdis IS NOT NULL -- foi ativado
ORDER BY D1.nomdis ASC
