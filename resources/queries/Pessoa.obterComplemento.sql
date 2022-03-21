SELECT DISTINCT PESSOA.codpes, -- Código da pessoa
	PESSOA.nompes, -- Nome da pessoa
	PESSOA.sexpes, -- Sexo da pessoa
	PESSOA.dtanas, -- Data de nascimento
	PESSOA.nommaepes, -- Nome da mãe
	COMPLPESSOA.nompaipes, -- Nome do pai
	PESSOA.tipdocidf, -- Tipo documento de identificação 
	PESSOA.numdocidf, -- Número do documento de identificação
	PESSOA.numdocfmt, -- Número do documento de identificação formatado
	PESSOA.dtaexdidf, -- Data de expedição do documento de identificação
	PESSOA.dtafimvalidf, -- Data final de validade do documento de identificação
	PESSOA.sglorgexdidf, -- Sigla do Orgão de expedição do documento de identificação
	PESSOA.sglest, -- Sigla do estado, região, província (nacional ou internacional), onde foi expedido o documento de identificação
	PESSOA.numcpf, -- Número do CPF
	COMPLPESSOA.numtitelc, -- Número do título de eleitor
	COMPLPESSOA.numsectitelc, -- Número da seção do título de eleitor
	COMPLPESSOA.numzontitelc, -- Número da zona do título de eleitor
	localidade_titulo_eleitor.cidloc AS cidade_titulo_eleitor, -- Cidade do título de eleitor
	localidade_titulo_eleitor.sglest AS estado_titulo_eleitor, -- Estado do título de eleitor
	COMPLPESSOA.numcermil, -- Número do certificado militar
	COMPLPESSOA.sercermil, -- Série do certificado militar
	COMPLPESSOA.codorgcermil, -- Código do órgão do certificado militar
	COMPLPESSOA.codrgimil, -- Código da região do certificado militar
	COMPLPESSOA.epforgcermil, -- Especificação do órgão do certificado militar
	COMPLPESSOA.dtaemicermil, -- Data de emissão do certificado militar
	TIPOCERTIFMILITAR.nomctgcermil, -- Nome da categoria do certificado militar
	COMPLPESSOA.estciv, -- Estado civil
	COMPLPESSOA.nomcjg, -- Nome do conjuge
	COMPLPESSOA.numpss, -- Número passaporte
	COMPLPESSOA.dtaexdpss, -- Data de expedição do passaporte
	COMPLPESSOA.dtafimvalpss, -- Data de fim de validade do passaporte
	COMPLPESSOASERV.numdoctrb, -- Número da carteira de trabalho
	COMPLPESSOASERV.serdoctrb, -- Série da carteira de trabalho
	COMPLPESSOASERV.sglesttrb, -- Sigla do estado da carteira de trabalho
	localidade_nascimento.cidloc AS cidade_nascimento, -- Cidade de nascimento
	localidade_nascimento.sglest AS estado_nascimento, -- Estado de nascimento
	PAIS.nompas, -- País de nascimento
	PAIS.nacpas -- Nacionalidade

FROM PESSOA 
LEFT OUTER JOIN VINCULOPESSOAUSP ON VINCULOPESSOAUSP.codpes = PESSOA.codpes 
LEFT OUTER JOIN COMPLPESSOA ON COMPLPESSOA.codpes = PESSOA.codpes 
LEFT OUTER JOIN LOCALIDADE AS localidade_nascimento ON localidade_nascimento.codloc = COMPLPESSOA.codlocnas 
LEFT OUTER JOIN PAIS ON PAIS.codpas = COMPLPESSOA.codpasnac 
LEFT OUTER JOIN LOCALIDADE AS localidade_titulo_eleitor ON localidade_titulo_eleitor.codloc = COMPLPESSOA.codloctit 
LEFT OUTER JOIN TIPOCERTIFMILITAR ON TIPOCERTIFMILITAR.ctgcermil = COMPLPESSOA.ctgcermil 
LEFT OUTER JOIN COMPLPESSOASERV ON COMPLPESSOASERV.codpes = PESSOA.codpes
WHERE PESSOA.codpes = convert(int,:codpes)
