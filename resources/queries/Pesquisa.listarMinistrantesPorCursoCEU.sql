select mini.codofeatvceu, mini.codpes, p.nompes 
from MINISTRANTECEU mini
inner join PESSOA p on p.codpes = mini.codpes
where mini.codofeatvceu = convert(int,:codofeatvceu)
order by p.nompes asc