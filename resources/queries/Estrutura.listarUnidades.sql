SELECT  C.*, U.* FROM UNIDADE U
INNER JOIN CAMPUS C ON U.codcam = C.codcam AND C.numpticam = U.numpticam
WHERE U.dtadtvund IS NULL 
ORDER BY C.nomofccam, U.nomund
