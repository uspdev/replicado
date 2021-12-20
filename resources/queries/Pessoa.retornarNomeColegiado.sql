SELECT nomclg FROM COLEGIADO
WHERE COLEGIADO.codundrsp IN (__unidades__)
  AND COLEGIADO.codclg = convert(int,:codclg)
  AND COLEGIADO.sglclg = :sglclg