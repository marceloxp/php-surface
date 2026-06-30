# Proposal: Site de documentação com Material MkDocs

## Why

O **php-surface** já tem um MVP estável com CLI documentada apenas no `README.md`. Para ser o **site oficial do projeto**, precisamos de documentação em inglês navegável, pesquisável e publicável — com referência de comandos, exemplos de saída reais e um guia de instalação no **Claude Code**. Material MkDocs oferece tema moderno, busca integrada e deploy simples para GitHub Pages, alinhado ao objetivo de uso por agentes de IA.

## What Changes

- Adicionar diretório `docs/` com conteúdo Markdown e `mkdocs.yml` na raiz do repositório.
- Configurar **Material for MkDocs** como tema (paleta, navegação, extensões Markdown).
- Criar página inicial com visão geral do projeto e link para repositório.
- Criar seção **Referência CLI** com todos os comandos, flags, variáveis de ambiente e códigos de saída.
- Criar seção **Exemplos de saída** com saídas JSON e `--text` derivadas dos snapshots de teste.
- Criar seção **Claude Code** ensinando como disponibilizar o `php-surface` para agentes (instalação, PATH, skill/CLAUDE.md).
- Configurar publicação do site via **GitHub Actions** → GitHub Pages (sem deploy manual).
- Manter `README.md` enxuto, apontando para o site como documentação canônica.

## Capabilities

### New Capabilities

- `docs-site`: Scaffold MkDocs + Material, navegação, tema, build local e deploy como site oficial.
- `cli-reference`: Documentação completa da CLI (comandos, opções, env vars, exit codes, fluxo incremental).
- `claude-code-install`: Guia de instalação e integração do php-surface no Claude Code.

### Modified Capabilities

- _(nenhuma — não há specs de produto existentes em `openspec/specs/`.)_

## Impact

| Área | Impacto |
|------|---------|
| Repositório | Novos arquivos: `mkdocs.yml`, `docs/**`, possivelmente `requirements-docs.txt`, workflow CI |
| README | Reduzido a quick start + link para o site |
| Dependências | Python (`mkdocs-material`); sem impacto no runtime PHP |
| Testes PHP | Nenhuma alteração obrigatória; exemplos de saída devem permanecer alinhados aos snapshots |
| Publicação | GitHub Pages em `https://marceloxp.github.io/php-surface/` (ou domínio customizado futuro) |

## Roadmap

Implementação dividida em fases — cada fase tem arquivo próprio em `roadmap/`:

| Fase | Arquivo | Entrega |
|------|---------|---------|
| 1 | [phase-01-scaffold.md](roadmap/phase-01-scaffold.md) | Scaffold MkDocs + Material, home page, build local |
| 2 | [phase-02-cli-reference.md](roadmap/phase-02-cli-reference.md) | Referência CLI completa |
| 3 | [phase-03-output-examples.md](roadmap/phase-03-output-examples.md) | Exemplos de saída (JSON e text) |
| 4 | [phase-04-claude-code.md](roadmap/phase-04-claude-code.md) | Guia de instalação no Claude Code |
| 5 | [phase-05-publish.md](roadmap/phase-05-publish.md) | Deploy GitHub Pages + CI |

## Workflow

Implementação fase a fase com revisão do maintainer antes de cada commit. Ver [WORKFLOW.md](WORKFLOW.md).

## Language & Scope

| Área | Idioma |
|------|--------|
| Site MkDocs (`docs/**`) | **English only** |
| Roadmap (`roadmap/**`) | pt-BR (planejamento interno) |
| Conversas com o maintainer | pt-BR |

**Explicitamente fora de escopo:** versionamento de docs (`mike`), i18n/multi-language, plugins de API reference PHP (ex.: `mkdocstrings` — não aplicável; php-surface é CLI, não SDK).

## Success Criteria

- `mkdocs serve` renderiza o site localmente sem erros.
- Toda flag documentada em `--help` aparece na referência CLI.
- Exemplos de saída correspondem aos snapshots em `tests/.pest/snapshots/`.
- Guia Claude Code permite um desenvolvedor configurar o tool em menos de 10 minutos.
- Site publicado e acessível como documentação oficial do projeto.
