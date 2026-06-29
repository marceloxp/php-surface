## Fase 8 — Modo expandido (`--full`)

**Objetivo:** detalhe sob demanda — parâmetros estruturados e docblock completo.

**Entrega:**

* `--full` adiciona por método:
  * `parameters[]` com `name`, `type`, `default` (omitir `default` se ausente)
  * `docblock.text` (docblock integral) em vez de só `summary`
* Sem `--full`: comportamento das fases 3–4 inalterado

**Como testar:**

```bash
./bin/php-surface tests/fixtures/Docblocks.php --full | jq '.symbols[].methods[] | select(.name=="charge")'
./bin/php-surface tests/fixtures/Methods.php --full | jq '.symbols[].methods[] | select(.name=="findActive") | .parameters'
./bin/php-surface tests/fixtures/Docblocks.php --full --filter pending | jq .
```

**Critérios de aceite:**

- [x] Sem `--full`, `charge` **não** tem `parameters`
- [x] Com `--full`, `charge` tem `parameters` com `Money $amount` correto
- [x] Com `--full`, docblock completo disponível (não só summary)
- [x] `--full` combinável com `--filter` e `--visibility`
- [x] Mapa default (sem `--full`) continua enxuto

**Próxima fase bloqueada até:** todos os itens passarem.