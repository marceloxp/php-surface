## Fase 6 — Filtro por nome (`--filter`)

**Objetivo:** reduzir métodos exibidos por correspondência de nome.

**Entrega:**

* `--filter <termo>` aplicado a JSON e `--text`
* Comportamento documentado: substring no nome do método (case-sensitive, salvo decisão contrária registrada no README)

**Como testar:**

```bash
./bin/php-surface tests/fixtures/Docblocks.php --filter charge | jq '.symbols[].methods[].name'
./bin/php-surface tests/fixtures/Docblocks.php --filter charge --text
./bin/php-surface tests/fixtures/Methods.php --filter find
```

**Critérios de aceite:**

- [ ] `--filter charge` retorna só `charge` (não `refund`, não `pending`)
- [ ] `--filter find` retorna `findActive`
- [ ] Filtro sem match → `methods` vazio ou mensagem clara (comportamento definido e consistente)
- [ ] Funciona com `--text` e JSON

**Próxima fase bloqueada até:** todos os itens passarem.