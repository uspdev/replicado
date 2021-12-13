
SELECT
  PARTICIPANTECOLEG.codpes as titular, 
  PARTICIPANTECOLEG.tipfncclg,
  PARTICIPANTECOLEG.dtainimdt,
  PARTICIPANTECOLEG.dtafimmdt

FROM PARTICIPANTECOLEG
  INNER JOIN COLEGIADO ON COLEGIADO.codclg=PARTICIPANTECOLEG.codclg
  -- Somente representações ativas
WHERE PARTICIPANTECOLEG.dtafimmdt > :dtafimmdt

-- Somente da unidade e colegiado em questão
  AND COLEGIADO.codundrsp IN (__unidades__)
  AND COLEGIADO.codclg = convert(int,:codclg)
  AND PARTICIPANTECOLEG.tipfncclg in ('Presidente','Vice-Presidente')

  UNION 
SELECT
  PARTICIPANTECOLEG.codpes as titular, 
  PARTICIPANTECOLEG.tipfncclg,
  PARTICIPANTECOLEG.dtainimdt,
  PARTICIPANTECOLEG.dtafimmdt

FROM PARTICIPANTECOLEG
  INNER JOIN COLEGIADO ON COLEGIADO.codclg=PARTICIPANTECOLEG.codclg
  -- Somente representações ativas
WHERE PARTICIPANTECOLEG.dtafimmdt > :dtafimmdt

-- Somente da unidade e colegiado em questão
  AND COLEGIADO.codundrsp IN (__unidades__)
  AND COLEGIADO.codclg = convert(int,:codclg)
  AND PARTICIPANTECOLEG.tipfncclg != 'Suplente'
  AND PARTICIPANTECOLEG.codpes NOT IN 
    (
    SELECT
    PARTICIPANTECOLEG.codpes  
  FROM PARTICIPANTECOLEG
    INNER JOIN COLEGIADO ON COLEGIADO.codclg=PARTICIPANTECOLEG.codclg
    -- Somente representações ativas
  WHERE PARTICIPANTECOLEG.dtafimmdt > :dtafimmdt
  -- Somente da unidade e colegiado em questão
    AND COLEGIADO.codundrsp IN (__unidades__)
    AND COLEGIADO.codclg = convert(int,:codclg)
    AND PARTICIPANTECOLEG.tipfncclg in ('Presidente','Vice-Presidente')
   
    )
  order by titular


  /**

SELECT
  PARTICIPANTECOLEG.codpes,
  PARTICIPANTECOLEG.tipfncclg,
  PARTICIPANTECOLEG.dtainimdt,
  PARTICIPANTECOLEG.dtafimmdt

FROM PARTICIPANTECOLEG
  INNER JOIN COLEGIADO ON COLEGIADO.codclg=PARTICIPANTECOLEG.codclg

  -- Somente representações ativas
WHERE PARTICIPANTECOLEG.dtafimmdt > :dtafimmdt

-- Somente da unidade e colegiado em questão
  AND COLEGIADO.codundrsp IN (__unidades__)
  AND COLEGIADO.codclg = convert(int,:codclg)
  AND PARTICIPANTECOLEG.tipfncclg != 'Suplente'
  **/