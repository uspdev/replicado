SELECT 	e.codcurceu, e.codedicurceu, c.nomcurceu, 
        c.codsetdep, s.nomset, c.objcur, c.juscur, 
        c.fmtcurceu, c.dscpbcinr, e.dtainiofeedi, 
        e.dtafimofeedi, e.qtdvagofe 
FROM EDICAOCURSOOFECEU e
LEFT JOIN CURSOCEU c ON c.codcurceu = e.codcurceu 	
LEFT JOIN SETOR S ON c.codsetdep = s.codset
WHERE 
	c.codclg in (__unidades__)
	__anos__
        __deptos__
	ORDER BY e.dtainiofeedi