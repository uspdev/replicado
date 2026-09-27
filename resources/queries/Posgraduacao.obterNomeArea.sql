SELECT nomare, nomareigl
FROM NOMEAREA
WHERE codare = convert(int, :codare)
    AND dtafimare IS NULL


