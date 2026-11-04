SELECT L.nomset, L.sglclgund
FROM LOCALIZAPESSOA L
WHERE L.codpes = convert(int, :codpes)
AND L.tipvin = :tipvin
