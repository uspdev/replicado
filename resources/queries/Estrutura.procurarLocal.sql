SELECT L.*, E.epflgr, E.numlgr, U.sglund
FROM LOCALUSP L
LEFT JOIN (
    SELECT codund, numseqendusp, MAX(epflgr) AS epflgr, MAX(numlgr) AS numlgr
    FROM ENDUSP
    GROUP BY codund, numseqendusp
    ) E  ON L.codund = E.codund AND L.numseqendusp = E.numseqendusp
LEFT JOIN UNIDADE U ON L.codund = U.codund
WHERE __filtro_codund__
    AND CONVERT(VARCHAR, L.codlocusp) LIKE :partCodlocusp;
