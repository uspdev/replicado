SELECT TOP 1 nompes 
from fflch.dbo.PDPROJETOSUPERVISOR p 
inner join fflch.dbo.PESSOA p2 on p.codpesspv = p2.codpes
where  __codprj__ 
ORDER BY anoprj DESC