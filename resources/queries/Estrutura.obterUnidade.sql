SELECT U.*, E.* 
FROM UNIDADE U, ENDUSP E 
WHERE U.codund = CONVERT(int, :codund)
AND (E.numseqendusp = 1 AND E.codund = U.codund)