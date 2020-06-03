## Recomendações para contribuir com este projeto

O replicado pode consultar tanto o MSSQL quanto o sybase-ase e em diversas versões diferentes. Dessa forma é necessário manter a compatibilidade com os diversos replicados das unidades.

* Abra uma issue antes de começar a mexer no código. A discussão prévia é importante para alinhar as idéias.
* As contribuições serão aceitas por meio de pull requests. Para tanto faça as alterações em uma branch issue_xx.
* Ao criar um novo método, lembre de documentar o DOCBLOCK, o código e gerar a documentação no `doc/` correspondente.
* Ao criar um novo método, crie o teste correspondente (TODO).
* Siga as edentações e outros padrões adotados nos demais métodos.
* A branch master é considerada estável e pode ser usada em produção, porém os releases têm sido regulares.
* Para os "benevolent dictators", a cada merge com ajuste no código faça um novo release para que o packagist seja atualizado. A versão estável que pode ser configurado no composer.json é 1.*

### SQL

* Deve-se usar ```getdate()``` ao invés de ```current_timestamp```
* Se um parâmetro passado for int deve-se fazer a conversão com ```convert(int, :param)```

Até então algumas queries estão assim

```
$query = "SELECT *";
$query .= " FROM PESSOA as p";
$query .= " WHERE p.codpes = :codpes";
```

Vamos procurar fazer assim
```
$query = "SELECT *
    FROM PESSOA as p
    WHERE p.codpes = convert(int, :codpes)
";
```

Fica mais conciso e facilita copiar e colar de um frontend de SGBD.


### PHP

* Deve-se adicionar o bloco phpdoc correspondente ao método
* Os argumentos dos métodos devem ser tipados, incluindo int, string etc que são suportados a partir do php 7.0
* Deve-se dar preferência para aspas simples em strings pois o PHP não tenta parsear seu conteúdo


### Compatibilidade 

Como essa biblioteca roda em vários servidores temos de atentar para a compatibilidade de versão do PHP. A versão mínima que deve ser suportada é o PHP 7.0 que vem por padrão no Debian 9 Stretch. O Debian 10 Buster vem com php 7.3. O Ubuntu 18.04 vem com php 7.2.

O ideal é que seja testado em PHP 7.0 a php 7.3, cobrindo as principais distribuições em uso.