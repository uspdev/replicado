SELECT R.* FROM RESUSERVHISTFUNCIONAL R 
WHERE R.codpes = CONVERT(int, :codpes) 
AND R.dtafimsitfun IS NOT NULL
ORDER BY R.dtainisitfun 

