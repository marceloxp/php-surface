# Fase 4 — Instalação no Claude Code

**Objetivo:** Guia completo para disponibilizar o php-surface a agentes no Claude Code.

**Depende de:** [Fase 1 — Scaffold](phase-01-scaffold.md) (página stub já existe)

**Entrega:** `docs/integrations/claude-code.md`

---

## Escopo

### Público

Desenvolvedores que usam **Claude Code** (CLI da Anthropic) em projetos PHP e querem que o agente explore arquivos grandes incrementalmente, sem ler o arquivo inteiro.

### Estrutura do guia

#### 1. Pré-requisitos

- PHP 8.3+ no PATH (ou `PHP_SURFACE_BIN_PATH`)
- php-surface instalado no projeto ou globalmente
- Claude Code instalado e autenticado

#### 2. Instalação no projeto (recomendado)

```bash
# Opção A — clone como ferramenta no monorepo
git clone https://github.com/marceloxp/php-surface.git tools/php-surface
cd tools/php-surface && composer install --no-dev

# Opção B — composer path repository (se publicado no Packagist futuramente)
# Opção C — symlink do binário após clone local
```

Verificar:

```bash
./tools/php-surface/bin/php-surface --version
```

#### 3. Tornar o binário invocável

Documentar opções:

| Abordagem | Como |
|-----------|------|
| Path relativo | Agente usa `./tools/php-surface/bin/php-surface File.php` |
| PATH no shell profile | `export PATH="$PWD/tools/php-surface/bin:$PATH"` |
| `PHP_SURFACE_BIN_PATH` | Quando o wrapper precisa de PHP específico |

#### 4. CLAUDE.md — instruções para o agente

Snippet pronto para copiar no `CLAUDE.md` do projeto PHP:

```markdown
## PHP structural exploration

Before reading a PHP file larger than ~200 lines, run php-surface:

1. `php-surface path/to/File.php --stats` — overview
2. `php-surface path/to/File.php --filter <method>` — narrow methods
3. `php-surface path/to/File.php --show ClassName::method` — read body only when needed

Default output is JSON. Use `--text` for human-readable maps.
If exit code is 3, output exceeded 8 KB — use filters, --stats, or --search instead of --allow-large-output unless necessary.

Tool location: ./tools/php-surface/bin/php-surface
```

Ajustar path conforme instalação do usuário.

#### 5. Permissões Claude Code

Explicar que o agente precisa permissão para executar o binário (Bash tool). Sugerir adicionar o path em allowlist de comandos seguros do projeto, se aplicável à versão do Claude Code em uso.

#### 6. Workflow incremental (diagrama)

```
Agent receives task involving Large.php
        │
        ▼
php-surface Large.php --stats
        │
        ├─ small surface? → --show for specific method
        │
        └─ many methods? → --filter / --search / --visibility public
                │
                ▼
        php-surface Large.php --show TargetClass::method
                │
                ▼
        Read only the extracted source lines
```

#### 7. Variáveis de ambiente úteis

| Variável | Uso no Claude Code |
|----------|-------------------|
| `PHP_SURFACE_MAX_OUTPUT_BYTES` | Aumentar limite se contexto do agente suportar (com cautela) |
| `PHP_SURFACE_BIN_PATH` | Forçar PHP 8.3 quando `php` default é 8.2 |

#### 8. Troubleshooting

| Problema | Solução |
|----------|---------|
| `command not found` | Verificar path no CLAUDE.md |
| Exit code 2 parse error | Arquivo PHP inválido — não é bug do surface |
| Exit code 3 | Usar `--stats`, `--filter`, `--search` conforme hints stderr |
| Agente ignora a ferramenta | Reforçar regra no CLAUDE.md; mencionar antes de Read tool |

#### 9. Skill opcional (avançado)

Breve nota: usuários podem encapsular o workflow em uma **skill** Claude Code / Cursor (`.cursor/skills/` ou equivalente) com triggers "explore PHP file", "php surface". Link para documentação de skills quando o usuário quiser automatizar — **não implementar skill nesta fase**, apenas documentar a possibilidade.

---

## Critérios de aceite

- [ ] Instalação passo a passo testada manualmente
- [ ] Snippet CLAUDE.md copy-paste ready
- [ ] Workflow incremental com diagrama
- [ ] Troubleshooting com ≥4 entradas
- [ ] Link de volta para CLI reference e examples
- [ ] Não assume Packagist (projeto ainda local/git)

---

## Fora de escopo

- Publicar skill oficial no marketplace
- MCP server wrapper
- Plugin Anthropic first-party
