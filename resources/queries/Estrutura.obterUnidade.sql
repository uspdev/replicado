SELECT U.*, E.*, L.*
FROM UNIDADE U, ENDUSP E, LOCALIDADE L
WHERE U.codund = CONVERT(int, :codund)
AND (E.numseqendusp = 1 AND E.codund = U.codund)
AND L.codloc = E.codloc