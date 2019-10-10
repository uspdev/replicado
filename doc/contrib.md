## Recomendações para contribuir com este projeto

O replicado pode consultar tanto o MSSQL quanto o sybase-ase e em diversas versões diferentes. Dessa forma é necessário manter a compatibilidade com os diversos replicados das unidades.

* Procure abrir uma issue antes de começar a mexer no código.
* As contribuições serão aceitas por meio de pull requests.
* Ao criar um novo método atente para a documentação, tanto no código quanto no readme.
* Ao criar um novo método, crie o teste correspondente.
* A branch master é considerada estável e em geral é usado em produção. 
* Os releases não são muito regulares, daí novamente usar a branch master em produção.

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


### Compatibilidade 

Como essa biblioteca roda em vários servidores temos de atentar para a compatibilidade de versão do PHP. A versão mínima que deve ser suportada é o PHP 7.0 que vem por padrão no Debian 9 Stretch. O Debian 10 Buster vem com php 7.3. O Ubuntu 18.04 vem com php 7.2.

O ideal é que seja testado em PHP 7.0 a php 7.3, cobrindo as principais distribuições em uso.