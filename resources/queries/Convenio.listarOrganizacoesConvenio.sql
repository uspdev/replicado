SELECT 
    co.codcvn,
    co.codorg,
    o.nomrazsoc AS nomeOrganizacao
FROM CONVORGAN co
JOIN ORGANIZACAO o ON o.codorg = co.codorg
WHERE co.codcvn = convert(int,:codcvn)