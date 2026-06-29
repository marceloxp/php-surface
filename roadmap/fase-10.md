## Fase 10 — Robustez e runtime

**Objetivo:** comportamento previsível em borda e configuração de PHP.

**Entrega:**

* Exit codes documentados (`0` ok, `1` uso, `2` arquivo/parse inválido — ou tabela no README)
* Arquivo PHP vazio ou sem símbolos → JSON válido (`symbols: []`)
* Erro de sintaxe PHP → mensagem útil, sem stack trace ruidoso
* Suporte a `PHP_SURFACE_BIN_PATH` (wrapper ou documentação de invocação)

**Como testar:**

```bash
./bin/php-surface tests/fixtures/Empty.php | jq .
./bin/php-surface tests/fixtures/SyntaxError.php    # deve falhar graciosamente
./bin/php-surface tests/fixtures/Methods.php | diff - <(./bin/php-surface tests/fixtures/Methods.php)
PHP_SURFACE_BIN_PATH=/caminho/para/php ./bin/php-surface tests/fixtures/Methods.php | jq .file
```

**Critérios de aceite:**

- [ ] Arquivo vazio → `{"file":"...","symbols":[]}` (ou equivalente)
- [ ] Syntax error → exit ≠ 0, mensagem compreensível
- [ ] Duas execuções no mesmo arquivo → diff vazio
- [ ] `PHP_SURFACE_BIN_PATH` documentado e funcional (se wrapper shell existir)

**MVP concluído quando:** fase 10 aprovada.