SELECT DISTINCT i.codpesalu FROM ICTPROJEDITALBOLSA b
inner join ICTPROJETO i on i.codprj = b.codprj 
where codundprj = 8
and codmdl = 1
and i.codpesalu  = convert(int,:codpes)
and i.codprj = convert(int,:codprj)

