SELECT V.codpes, V.nompes, S.nomabvset, V.sitoco, V.dtainisitoco, V.dtafimsitoco
FROM VINCULOPESSOAUSP V INNER JOIN SETOR S ON V.codset = S.codset
WHERE V.codund IN (__codundclgs__) AND V.dtafimsitoco > DATEADD(DAY, -1, GETDATE())
ORDER BY V.nompes
