# Design: Site de documentação Material MkDocs

## Context

O php-surface é uma CLI PHP 8.3+ que mapeia a superfície estrutural de arquivos `.php` (classes, traits, interfaces, métodos). O público-alvo inclui desenvolvedores e agentes de IA que exploram codebases incrementalmente. A documentação deve ser o **site do projeto**, não um README longo.

Stack escolhida com base no skill [Material MkDocs](https://github.com/TerminalSkills/skills/tree/main/skills/mkdocs):

- `mkdocs-material` — tema, busca, navegação SPA, code copy
- Markdown com extensões pymdownx (admonitions, tabs, superfences)
- Deploy estático via GitHub Pages

## Goals / Non-Goals

**Goals:**

- Site oficial em inglês navegável com busca full-text.
- Referência CLI completa e exemplos de saída verificáveis (snapshots).
- Guia prático de integração com Claude Code.
- Build reproduzível localmente (`mkdocs serve` / `mkdocs build`).
- Deploy automatizado no merge para `main`.

**Non-Goals:**

- Versionamento de docs (`mike` ou similar) — site único, sempre “latest”.
- i18n / multi-language — **English only** em todo conteúdo publicado (`docs/**`).
- API reference PHP auto-gerada (plugins como `mkdocstrings`) — php-surface é uma CLI invocável, não uma biblioteca PHP consumida via autoload; referência manual da CLI é suficiente.
- Blog ou changelog automatizado.
- Deploy manual (`mkdocs gh-deploy` local) — apenas GitHub Actions.

### Language policy

- **`docs/**`** — English only (prose, headings, admonitions, nav titles in `mkdocs.yml`).
- **`roadmap/**`** — pt-BR (artefatos de planejamento; não publicados no site).
- **`README.md`** — English (GitHub landing; link para o site).

## Decisions

### 1. Estrutura de diretórios

```
php-surface/
├── mkdocs.yml
├── requirements-docs.txt      # mkdocs-material pinned
├── docs/
│   ├── index.md               # Home
│   ├── getting-started/
│   │   ├── installation.md
│   │   └── quickstart.md
│   ├── cli/
│   │   ├── reference.md       # Todas as flags e env vars
│   │   ├── exit-codes.md
│   │   └── examples.md        # Saídas JSON + --text
│   └── integrations/
│       └── claude-code.md
└── site/                      # gitignored — build output
```

### 2. Configuração `mkdocs.yml` (Material)

Decisões principais:

| Opção | Valor | Motivo |
|-------|-------|--------|
| `site_name` | php-surface | Nome do projeto |
| `site_url` | `https://marceloxp.github.io/php-surface/` | GitHub Pages |
| `repo_url` | `https://github.com/marceloxp/php-surface` | Link edit on GitHub |
| `theme.name` | `material` | Padrão do skill |
| Dark/light toggle | Sim | Acessibilidade |
| `navigation.tabs` | Sim | Separa Getting Started / CLI / Integrations |
| `content.code.copy` | Sim | Copiar comandos facilmente |
| `pymdownx.tabbed` | Sim | Alternar JSON vs `--text` nos exemplos |

### 3. Fonte da verdade para conteúdo CLI

| Conteúdo | Fonte |
|----------|-------|
| Flags e help text | `src/Cli/Application.php::printHelp()` |
| Exit codes | `src/Cli/ExitCode.php` + README |
| Exemplos de saída | `tests/.pest/snapshots/Feature/CliSnapshotTest/` |
| Fixtures | `tests/fixtures/*.php` (referenciadas nos exemplos) |

### 4. Exemplos de saída — formato na doc

Usar **content tabs** do Material:

```markdown
=== "JSON (default)"

    ```json
    { ... }
    ```

=== "Text (--text)"

    ```text
    ...
    ```
```

Saídas copiadas dos snapshots; truncar apenas com comentário `// ...` quando necessário para legibilidade, nunca alterando estrutura.

### 5. Integração Claude Code

> Fazer entrevista com o Marcelo antes de tomar as decisões de integração.

Abordagem em camadas (documentar todas na Fase 4):

1. **Instalação local** — `composer install` + `./bin/php-surface` no PATH do projeto.
2. **CLAUDE.md** — instruções para o agente usar php-surface antes de ler arquivos grandes.
3. **Skill opcional** — skill `.cursor/skills/` ou equivalente Claude Code com workflow de exploração incremental (`--stats` → `--filter` → `--show`).
4. **Variáveis de ambiente** — `PHP_SURFACE_BIN_PATH`, `PHP_SURFACE_MAX_OUTPUT_BYTES`.

Não criar MCP server nesta fase — a CLI é suficiente e offline.

### 6. Publicação (GitHub Actions only)

Deploy exclusivamente via workflow `.github/workflows/docs.yml`:

- Trigger: push para `main` quando `docs/**`, `mkdocs.yml` ou `requirements-docs.txt` mudam (+ `workflow_dispatch`).
- Build: `mkdocs build --strict` no runner Ubuntu.
- Deploy: GitHub Actions nativo (artifact + `deploy-pages`) **ou** push para branch `gh-pages` via action — ver [phase-05-publish.md](roadmap/phase-05-publish.md) para passo a passo de configuração no GitHub.
- Adicionar `site/` ao `.gitignore`.

Na Fase 5, o agente MUST entregar instruções passo a passo para o maintainer configurar GitHub Pages no repositório (Settings → Pages → Source: GitHub Actions).

### 7. README enxuto

Após publicação, README mantém:

- One-liner + badge link para docs
- Install mínimo (`composer install`)
- Um exemplo
- Link "Full documentation →"

## Risks / Trade-offs

| Risco | Mitigação |
|-------|-----------|
| Exemplos desatualizados vs snapshots | Checklist na Fase 3: copiar de snapshots; revisar em PRs que alteram output |
| Dependência Python separada do PHP | `requirements-docs.txt` + instrução no README/CI; não misturar com Composer |
| Duplicação README vs docs | README aponta para site; docs são canônicas |
| GitHub Pages URL muda com fork | `site_url` configurável; forks podem desabilitar deploy |
