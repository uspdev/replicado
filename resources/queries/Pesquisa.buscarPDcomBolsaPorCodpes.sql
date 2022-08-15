SELECT *
from PDPROGRAMAFOMENTO  
where codprj = convert(int,:codprj)
and anoprj = convert(int,:anoprj)