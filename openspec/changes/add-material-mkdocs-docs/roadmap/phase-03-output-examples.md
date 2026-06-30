# Fase 3 — Exemplos de saída

**Objetivo:** Seção com comandos e exemplos de saída real (JSON e `--text`), derivados dos snapshot tests.

**Depende de:** [Fase 2 — Referência CLI](phase-02-cli-reference.md)

**Entrega:** `docs/cli/examples.md` completo.

---

## Escopo

### Estrutura de `docs/cli/examples.md`

Organizar por caso de uso. Cada seção: **comando**, **descrição**, **tabs JSON / text**.

| Seção | Comando | Snapshot fonte |
|-------|---------|----------------|
| Default map | `php-surface tests/fixtures/Methods.php` | `_json_output__→_methods_fixture.snap` |
| Text output | `... --text` | `_text_output__→_methods_fixture.snap` |
| Filter | `... --filter find` | `_filter_output__→_methods_fixture_by_find.snap` |
| Visibility | `... --visibility public --text` | `_visibility_output__→_methods_fixture_public_as_text.snap` |
| Show method | `... --show save` | `_show_output__→_methods_fixture_save.snap` |
| Stats | `... --stats --text` | `_stats_output__→_monster_fixture_as_text.snap` |
| Full mode | `... --full` | `_full_output__→_methods_fixture.snap` |
| Docblocks | `... --full` (fixture Docblocks) | `_full_output__→_docblocks_fixture.snap` |
| Empty file | `php-surface tests/fixtures/Empty.php` | `_json_output__→_empty_fixture.snap` |

### Fixture de referência

Documentar que exemplos usam fixtures em `tests/fixtures/`:

- `Methods.php` — classe com métodos public/protected, static, final, abstract
- `Docblocks.php` — docblocks e filtros
- `Monster.php` — arquivo grande para `--stats`
- `Empty.php` — arquivo sem símbolos

### Formato Material — content tabs

Para cada exemplo:

```markdown
### Mapa estrutural (default)

```bash
./bin/php-surface tests/fixtures/Methods.php
```

=== "JSON"

    ```json
    { ... snapshot ... }
    ```

=== "Text (--text)"

    ```bash
    ./bin/php-surface tests/fixtures/Methods.php --text
    ```

    ```text
    ... snapshot ...
    ```
```

### Output blocked (exit 3)

Incluir seção com exemplo de saída no **stderr** quando limite excedido (derivado de `OutputGuard` + testes em `CliTest.php`). Mostrar hints sugeridos.

### Notas

- `!!! note` — JSON é sparse by omission (sem keys vazias)
- `!!! tip` — preferir `--stats` antes de mapa completo em arquivos grandes

---

## Processo de manutenção

Quando snapshots mudarem:

1. Rodar `./vendor/bin/pest --update-snapshots` (intencional)
2. Atualizar `docs/cli/examples.md` com novos golden files
3. PR deve mencionar alinhamento snapshot ↔ docs

Opcional (fase futura): script que extrai snapshots para `docs/includes/` — **fora de escopo** desta fase.

---

## Critérios de aceite

- [ ] Mínimo 8 cenários documentados (tabela acima)
- [ ] Saídas JSON batem com snapshots (diff manual ou script)
- [ ] Tabs JSON/text renderizam no Material
- [ ] Comandos usam `php-surface` no PATH com caminhos absolutos (ver [installation.md](../../../docs/getting-started/installation.md))
- [ ] Seção exit 3 com exemplo stderr

---

## Fora de escopo

- Snapshots para `--search` (adicionar quando existirem testes dedicados)
- Geração automática de examples a partir de pest
