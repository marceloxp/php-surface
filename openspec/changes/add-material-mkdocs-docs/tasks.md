# Tasks: Site de documentação Material MkDocs

Checklist mestre. Detalhes e critérios de aceite por fase estão em `roadmap/phase-*.md`.

## Fase 1 — Scaffold

- [x] 1.1 Criar `requirements-docs.txt` com `mkdocs-material`
- [x] 1.2 Criar `mkdocs.yml` com tema Material e navegação
- [x] 1.3 Criar `docs/index.md` (home)
- [x] 1.4 Criar `docs/getting-started/installation.md` e `quickstart.md`
- [x] 1.5 Adicionar `site/` ao `.gitignore`
- [x] 1.6 Validar `mkdocs serve` e `mkdocs build`

→ [roadmap/phase-01-scaffold.md](roadmap/phase-01-scaffold.md)

## Fase 2 — Referência CLI

- [ ] 2.1 Criar `docs/cli/reference.md` com sintaxe, flags e env vars
- [ ] 2.2 Criar `docs/cli/exit-codes.md`
- [ ] 2.3 Documentar fluxo incremental de exploração (`--stats` → `--filter` → `--show`)
- [ ] 2.4 Documentar output size guard e `--allow-large-output`
- [ ] 2.5 Cross-check com `Application.php::printHelp()`

→ [roadmap/phase-02-cli-reference.md](roadmap/phase-02-cli-reference.md)

## Fase 3 — Exemplos de saída

- [ ] 3.1 Criar `docs/cli/examples.md`
- [ ] 3.2 Adicionar exemplos default map (JSON + `--text`)
- [ ] 3.3 Adicionar exemplos `--filter`, `--visibility`, `--search`, `--show`, `--stats`, `--full`
- [ ] 3.4 Usar content tabs JSON / text
- [ ] 3.5 Validar contra snapshots

→ [roadmap/phase-03-output-examples.md](roadmap/phase-03-output-examples.md)

## Fase 4 — Claude Code

- [ ] 4.1 Criar `docs/integrations/claude-code.md`
- [ ] 4.2 Documentar instalação no projeto
- [ ] 4.3 Documentar snippet CLAUDE.md recomendado
- [ ] 4.4 Documentar workflow incremental para agentes
- [ ] 4.5 Documentar variáveis de ambiente e troubleshooting

→ [roadmap/phase-04-claude-code.md](roadmap/phase-04-claude-code.md)

## Fase 5 — Publicação

- [ ] 5.1 Criar workflow GitHub Actions (build + deploy-pages)
- [ ] 5.2 Entregar instruções passo a passo para configurar GitHub Pages (Settings → GitHub Actions)
- [ ] 5.3 Atualizar README com link para o site (English)
- [ ] 5.4 Verificar `site_url` e links internos
- [ ] 5.5 Smoke test do site publicado (com maintainer)

→ [roadmap/phase-05-publish.md](roadmap/phase-05-publish.md)
