# Workflow — implementação das fases

Este change segue um ciclo de revisão **fase a fase**. O agente implementa; o maintainer confere; só então há commit e avanço.

## Ciclo por fase

```
Agente implementa fase N
        │
        ▼
Maintainer revisa (mkdocs serve, leitura, testes)
        │
        ├─ correções necessárias → agente ajusta → revisar de novo
        │
        └─ OK explícito → commit da fase N → agente inicia fase N+1
```

## Regras

| Regra | Detalhe |
|-------|---------|
| **Sem commit automático** | Ao terminar uma fase, o agente **não commita** — aguarda OK do maintainer. |
| **Um commit por fase aprovada** | Cada commit deve corresponder a uma fase concluída e revisada. |
| **Idioma do site** | `docs/**` e `mkdocs.yml` nav titles: **English only**. |
| **Idioma do planejamento** | `roadmap/**`, `proposal.md`, este arquivo: **pt-BR**. |
| **Fonte da verdade CLI** | `Application.php::printHelp()`, snapshots, `ExitCode.php`. |
| **Deploy** | Fase 5 — GitHub Actions; maintainer configura Pages no GitHub UI. |

## Status das fases

| Fase | Arquivo | Commit |
|------|---------|--------|
| 1 — Scaffold | [phase-01-scaffold.md](roadmap/phase-01-scaffold.md) | `3b15965` |
| 2 — CLI Reference | [phase-02-cli-reference.md](roadmap/phase-02-cli-reference.md) | approved |
| 3 — Output Examples | [phase-03-output-examples.md](roadmap/phase-03-output-examples.md) | — |
| 4 — Claude Code | [phase-04-claude-code.md](roadmap/phase-04-claude-code.md) | — |
| 5 — Publish | [phase-05-publish.md](roadmap/phase-05-publish.md) | — |

Atualizar a coluna **Commit** quando o maintainer aprovar e o commit for feito.

## Checklist do maintainer (cada fase)

- [ ] `mkdocs serve` — páginas novas/alteradas renderizam
- [ ] Conteúdo em inglês no site
- [ ] Alinhamento com código/snapshots (quando aplicável)
- [ ] OK explícito antes do próximo commit
