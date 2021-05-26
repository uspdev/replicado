SELECT DISTINCT i.codpesalu FROM ICTPROJEDITALBOLSA b
inner join ICTPROJETO i on i.codprj = b.codprj 
where codundprj = convert(int,:codundprj)
and (i.staprj = 'Ativo' OR i.staprj = 'Inscrito')
AND (i.dtafimprj > GETDATE() or i.dtafimprj IS NULL)
and codmdl = 1
and i.codpesalu  = convert(int,:codpes)