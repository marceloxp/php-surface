## Fase 9 — Drill-down (`--show`)

**Objetivo:** extrair o corpo do método (ou símbolo) pelas linhas do arquivo, sem executar código.

**Entrega:**

* `--show <nome>` — match por nome de método; múltiplos matches → todos retornados
* `--show ClassName::methodName` — desambiguação
* Usa `startLine`/`endLine` do mapa (ou equivalente interno)
* Saída: trecho de código fonte (formato JSON ou texto — definir na implementação e documentar)

**Como testar:**

```bash
./bin/php-surface tests/fixtures/Methods.php --show save
./bin/php-surface tests/fixtures/Methods.php --show OrderService::findActive
./bin/php-surface tests/fixtures/Symbols.php --show read        # múltiplos matches esperados
```

**Critérios de aceite:**

- [ ] `--show save` retorna linhas 42–59 (ou range real da fixture) com corpo do método
- [ ] `ClassName::methodName` retorna match único correto
- [ ] Nome ambíguo (`read` em interface + class) → **todos** os matches, não erro silencioso
- [ ] Nome inexistente → mensagem clara, exit ≠ 0
- [ ] Nenhum `require`/`include` do arquivo analisado (confirmar: código com `exit` ou side-effect no top-level não roda)

**Próxima fase bloqueada até:** todos os itens passarem.