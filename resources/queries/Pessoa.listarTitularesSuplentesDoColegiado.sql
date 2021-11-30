SELECT
  PARTICIPANTECOLEG.codpes as titular, 
  PARTICIPANTECOLEGSUPL.codpessup as suplente,
  PARTICIPANTECOLEG.tipfncclg,
  PARTICIPANTECOLEG.dtainimdt,
  PARTICIPANTECOLEG.dtafimmdt

FROM PARTICIPANTECOLEG
  INNER JOIN COLEGIADO ON COLEGIADO.codclg=PARTICIPANTECOLEG.codclg
  LEFT JOIN
  	PARTICIPANTECOLEGSUPL ON ( PARTICIPANTECOLEGSUPL.codpesttu=PARTICIPANTECOLEG.codpes
    AND PARTICIPANTECOLEGSUPL.codclgttu=PARTICIPANTECOLEG.codclg )

  -- Somente representações ativas
WHERE PARTICIPANTECOLEG.dtafimmdt > :dtafimmdt

-- Somente da unidade e colegiado em questão
  AND COLEGIADO.codundrsp IN (__unidades__)
  AND COLEGIADO.codclg = convert(int,:codclg)
  AND PARTICIPANTECOLEG.tipfncclg != 'Suplente'