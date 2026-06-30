# Fase 1 — Scaffold MkDocs + Material

**Objetivo:** Ter o site rodando localmente com estrutura de navegação e home page.

**Depende de:** nada (primeira fase).

**Entrega:** `mkdocs serve` funcional em `http://127.0.0.1:8000`.

**Idioma do site:** todo conteúdo em `docs/**` e títulos de navegação no `mkdocs.yml` MUST be **English only**. Este arquivo de roadmap permanece em pt-BR.

---

## Escopo

### Arquivos a criar

| Arquivo | Descrição |
|---------|-----------|
| `requirements-docs.txt` | `mkdocs-material>=9.5,<10` (pin na implementação) |
| `mkdocs.yml` | Configuração completa Material |
| `docs/index.md` | Home (EN) — what php-surface is, audience, GitHub link |
| `docs/getting-started/installation.md` | PHP 8.3+ requirements, `composer install` |
| `docs/getting-started/quickstart.md` | First command, default output, next steps |

### Arquivos a alterar

| Arquivo | Alteração |
|---------|-----------|
| `.gitignore` | Adicionar `site/` |

### `mkdocs.yml` — navegação inicial

```yaml
nav:
  - Home: index.md
  - Getting Started:
    - Installation: getting-started/installation.md
    - Quick Start: getting-started/quickstart.md
  - CLI Reference:
    - Commands: cli/reference.md          # stub na Fase 1
    - Exit Codes: cli/exit-codes.md       # stub
    - Output Examples: cli/examples.md    # stub
  - Integrations:
    - Claude Code: integrations/claude-code.md  # stub
```

Stubs podem conter `!!! info "Coming soon"` até as fases correspondentes.

### Features Material (mínimo viável)

- `navigation.instant`, `navigation.tabs`, `navigation.sections`
- `search.suggest`, `search.highlight`
- `content.code.copy`
- Paleta light/dark (indigo ou teal — alinhar à identidade do projeto)
- Extensões: `admonition`, `pymdownx.details`, `pymdownx.superfences`, `pymdownx.tabbed`, `pymdownx.highlight`, `toc.permalink`

### Conteúdo da home (`docs/index.md`)

Seções sugeridas:

1. **Hero** — título, tagline ("Structural explorer for PHP source files")
2. **Problem / Solution** — contexto para IA (2 parágrafos, derivado do README/proposal original)
3. **Quick example** — um bloco de comando + saída curta
4. **Features** — offline, deterministic, JSON-first, incremental exploration
5. **Links** — GitHub, Getting Started

---

## Critérios de aceite

- [ ] `pip install -r requirements-docs.txt` instala sem erro
- [ ] `mkdocs serve` abre site sem warnings críticos
- [ ] `mkdocs build` gera `site/` com todas as páginas
- [ ] Navegação por tabs funciona (Getting Started, CLI, Integrations)
- [ ] Busca encontra "php-surface" na home
- [ ] Dark mode toggle funciona

---

## Comandos de verificação

```bash
python3 -m venv .venv-docs
source .venv-docs/bin/activate
pip install -r requirements-docs.txt
mkdocs serve
mkdocs build --strict
```

`--strict` falha em links quebrados — usar quando stubs forem substituídos por conteúdo real (Fase 2+).

---

## Fora de escopo

- Conteúdo completo da referência CLI (Fase 2)
- Exemplos de saída (Fase 3)
- Claude Code (Fase 4)
- Deploy (Fase 5)
