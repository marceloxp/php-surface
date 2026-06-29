## Fase 7 — Filtro por visibilidade (`--visibility`)

**Objetivo:** restringir métodos por `public`, `protected` ou `private`.

**Entrega:**

* `--visibility public|protected|private`
* Combinável com `--filter`

**Como testar:**

```bash
./bin/php-surface tests/fixtures/Methods.php --visibility public | jq '.symbols[].methods[].name'
./bin/php-surface tests/fixtures/Methods.php --visibility protected --filter find | jq .
./bin/php-surface tests/fixtures/Methods.php --visibility public --text
```

**Critérios de aceite:**

- [x] `--visibility public` → só `save` e `lock` (conforme fixture)
- [x] `--visibility protected` → só métodos protected
- [x] `--visibility public --filter save` → interseção correta
- [x] Valor inválido → erro em stderr, exit ≠ 0

**Próxima fase bloqueada até:** todos os itens passarem.