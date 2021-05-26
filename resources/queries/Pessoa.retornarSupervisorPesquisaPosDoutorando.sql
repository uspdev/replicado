SELECT TOP 1 nompes 
from PDPROJETOSUPERVISOR p 
inner join PESSOA p2 on p.codpesspv = p2.codpes
where  __codprj__ 
ORDER BY anoprj DESC