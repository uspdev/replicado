SELECT DISTINCT i.codpesalu FROM fflch.dbo.ICTPROJEDITALBOLSA b
inner join fflch.dbo.ICTPROJETO i on i.codprj = b.codprj 
where codundprj = 8
and (i.staprj = 'Ativo' OR i.staprj = 'Inscrito')
AND (i.dtafimprj > GETDATE() or i.dtafimprj IS NULL)
and codmdl = 1
and i.codpesalu  = convert(int,:codpes)