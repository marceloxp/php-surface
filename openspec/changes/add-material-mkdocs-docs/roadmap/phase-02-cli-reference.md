# Fase 2 — Referência CLI

**Objetivo:** Documentar todos os comandos, flags, variáveis de ambiente e o fluxo de exploração incremental.

**Depende de:** [Fase 1 — Scaffold](phase-01-scaffold.md)

**Entrega:** `docs/cli/reference.md` e `docs/cli/exit-codes.md` completos.

---

## Escopo

### `docs/cli/reference.md`

#### Sintaxe

```
php-surface <file.php> [options]
```

#### Argumentos

| Argumento | Descrição |
|-----------|-----------|
| `file.php` | Arquivo PHP a analisar (obrigatório, extensão `.php`) |

#### Opções

Documentar cada flag com: descrição, valor esperado, exemplo.

| Flag | Valor | Descrição |
|------|-------|-----------|
| `--text` | — | Saída legível em vez de JSON |
| `--filter <name>` | string | Métodos cujo nome contém `<name>` (case-sensitive) |
| `--search <term>` | string | Busca símbolos, métodos e linhas (case-insensitive) |
| `--visibility <level>` | `public` \| `protected` \| `private` | Filtra por visibilidade |
| `--show <symbol>` | string | Extrai corpo do método (`save` ou `ClassName::save`) |
| `--stats` | — | Resumo estatístico em vez do mapa completo |
| `--full` | — | Parâmetros estruturados e docblocks completos |
| `--allow-large-output` | — | Ignora limite de 8 KB |
| `-h`, `--help` | — | Ajuda |
| `-V`, `--version` | — | Versão |

#### Variáveis de ambiente

| Variável | Default | Descrição |
|----------|---------|-----------|
| `PHP_SURFACE_BIN_PATH` | _(auto)_ | Override do binário PHP 8.3+ usado por `bin/php-surface` |
| `PHP_SURFACE_MAX_OUTPUT_BYTES` | `8192` | Limite de bytes da saída stdout |

#### Modos de saída (visão geral)

Explicar os modos mutuamente exclusivos na prática:

1. **Default** — mapa estrutural JSON (ou `--text`)
2. **`--stats`** — contagens e top classes por métodos
3. **`--search <term>`** — matches com contexto de linha
4. **`--show <symbol>`** — source do método

Combináveis: `--filter`, `--visibility`, `--full`, `--text`, `--allow-large-output` (conforme aplicável).

#### Fluxo incremental recomendado

Diagrama ou lista numerada:

```
1. php-surface Large.php --stats          → decidir se vale explorar
2. php-surface Large.php --filter save    → focar métodos relevantes
3. php-surface Large.php --show Class::save → ler implementação
```

Alternativas quando output > 8 KB:

- `--search <term>`
- `--visibility public`
- `--allow-large-output`

Usar admonition `!!! tip` para o workflow AI-first.

### `docs/cli/exit-codes.md`

| Código | Nome | Quando |
|--------|------|--------|
| `0` | Success | Saída emitida com sucesso |
| `1` | Usage | Argumento ausente, flag inválida, símbolo não encontrado |
| `2` | File error | Arquivo inexistente, extensão inválida, erro de parse |
| `3` | Output too large | Saída excede limite; hints no stderr |

Incluir exemplos de mensagens de erro no stderr (usage, file not found, output blocked).

---

## Fonte da verdade

- `src/Cli/Application.php` — help, validações, hints
- `src/Cli/ExitCode.php` — constantes
- `src/Cli/OutputGuard.php` — mensagens de bloqueio
- `README.md` — tabela de exit codes (já existente)

---

## Critérios de aceite

- [ ] Toda flag de `--help` está documentada
- [ ] Toda env var usada no código está documentada
- [ ] Exit codes 0–3 com exemplos
- [ ] Fluxo incremental descrito com comandos reais
- [ ] Nenhuma flag inventada ou obsoleta
- [ ] Links internos para Fase 3 (examples) funcionam

---

## Fora de escopo

- Exemplos completos de stdout (Fase 3 — link "see examples")
- Instalação Claude Code (Fase 4)
