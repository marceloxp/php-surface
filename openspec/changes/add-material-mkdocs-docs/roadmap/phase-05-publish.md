# Fase 5 — Publicação (GitHub Pages via GitHub Actions)

**Objetivo:** Site publicado como documentação oficial do projeto, com deploy automático no merge para `main`.

**Depende de:** Fases 1–4 concluídas (conteúdo real, não stubs).

**Entrega:** Site live + README atualizado + workflow CI + **instruções para o maintainer configurar o GitHub**.

**Deploy:** exclusivamente GitHub Actions — sem `mkdocs gh-deploy` manual local.

---

## Escopo

### GitHub Pages

**URL alvo:** `https://marceloxp.github.io/php-surface/`

Configurar em `mkdocs.yml`:

```yaml
site_url: https://marceloxp.github.io/php-surface/
repo_url: https://github.com/marceloxp/php-surface
repo_name: marceloxp/php-surface
edit_uri: edit/main/docs/
```

### GitHub Actions — `.github/workflows/docs.yml`

Abordagem recomendada: **GitHub Pages nativo** (sem branch `gh-pages` manual).

```yaml
name: Deploy docs

on:
  push:
    branches: [main]
    paths:
      - 'docs/**'
      - 'mkdocs.yml'
      - 'requirements-docs.txt'
      - '.github/workflows/docs.yml'
  workflow_dispatch:

permissions:
  contents: read
  pages: write
  id-token: write

concurrency:
  group: pages
  cancel-in-progress: false

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - uses: actions/setup-python@v5
        with:
          python-version: '3.12'

      - name: Install MkDocs
        run: pip install -r requirements-docs.txt

      - name: Build site
        run: mkdocs build --strict

      - name: Upload artifact
        uses: actions/upload-pages-artifact@v3
        with:
          path: site

  deploy:
    needs: build
    runs-on: ubuntu-latest
    environment:
      name: github-pages
      url: ${{ steps.deployment.outputs.page_url }}
    steps:
      - name: Deploy to GitHub Pages
        id: deployment
        uses: actions/deploy-pages@v4
```

**Por que esta abordagem:** o GitHub gerencia o hosting; o workflow só faz build + upload do artifact. Não precisa commitar `site/` nem manter branch `gh-pages`.

### README.md

Substituir corpo longo por:

- Link proeminente: **Documentation → https://marceloxp.github.io/php-surface/**
- Requirements + install mínimo (English)
- Um exemplo de comando
- Link para contribuir / testes

---

## Instruções para o maintainer — configurar no GitHub

> Quando chegarmos nesta fase, siga estes passos **uma vez** no repositório `marceloxp/php-surface`. O agente implementará o workflow; você habilita Pages no GitHub UI.

### Passo 1 — Merge do workflow

1. Confirme que `.github/workflows/docs.yml` está na branch `main`.
2. Vá em **Actions** no GitHub e verifique se o workflow "Deploy docs" aparece na lista.

### Passo 2 — Habilitar GitHub Pages

1. Abra o repositório no GitHub.
2. **Settings** → **Pages** (menu lateral).
3. Em **Build and deployment**:
   - **Source:** selecione **GitHub Actions** (não "Deploy from a branch").
4. Salve. Não é necessário escolher branch `gh-pages`.

### Passo 3 — Permissões do workflow (se o deploy falhar)

1. **Settings** → **Actions** → **General**.
2. Em **Workflow permissions**, escolha **Read and write permissions** *ou* mantenha read-only se usar apenas `deploy-pages` com `pages: write` (como no YAML acima).
3. Se usar environment `github-pages`:
   - **Settings** → **Environments** → **github-pages**
   - Verifique se não há protection rules bloqueando deploy da branch `main`.

### Passo 4 — Primeiro deploy

1. Faça push de qualquer alteração em `docs/**` ou dispare manualmente:
   - **Actions** → **Deploy docs** → **Run workflow**.
2. Aguarde os jobs `build` e `deploy` ficarem verdes.
3. Em **Settings → Pages**, o GitHub mostrará a URL publicada (pode levar 1–2 minutos na primeira vez).

### Passo 5 — Link no repositório

1. Na página principal do repo, clique **⚙️** ao lado de "About".
2. Marque **Use your GitHub Pages website**.
3. A URL `https://marceloxp.github.io/php-surface/` aparecerá no sidebar.

### Passo 6 — Verificação

Checklist pós-configuração:

- [ ] `https://marceloxp.github.io/php-surface/` carrega a home
- [ ] Busca encontra flags CLI (`--stats`, `--show`)
- [ ] Links "Edit on GitHub" abrem o arquivo correto em `docs/`
- [ ] Push em `docs/` dispara novo deploy automaticamente
- [ ] `mkdocs build --strict` passa no CI

### Troubleshooting comum

| Sintoma | Causa provável | Solução |
|---------|----------------|---------|
| Workflow não aparece | YAML não está em `main` | Merge PR com o workflow |
| `build` falha | Link quebrado no Markdown | Rodar `mkdocs build --strict` localmente e corrigir |
| `deploy` falha com 403 | Pages não habilitado ou permissões | Passo 2 e 3 acima |
| Site 404 após deploy verde | Propagação DNS/CDN | Aguardar ~5 min; hard refresh |
| URL errada em assets | `site_url` incorreto no `mkdocs.yml` | Conferir `site_url` com a URL real do Pages |

### Domínio customizado (opcional, futuro)

Se no futuro quiser `docs.seudominio.com`:

1. Adicionar `CNAME` em `docs/CNAME` ou via Settings → Pages → Custom domain.
2. Configurar registro DNS (A/CNAME) no provedor.
3. Atualizar `site_url` no `mkdocs.yml`.

Fora de escopo desta fase.

---

## Critérios de aceite

- [ ] Site acessível publicamente em GitHub Pages
- [ ] Deploy 100% via GitHub Actions (sem passos manuais recorrentes)
- [ ] README aponta para docs como fonte canônica
- [ ] `site/` no `.gitignore`
- [ ] `mkdocs build --strict` passa no CI
- [ ] Maintainer recebeu e executou os passos da seção "Instruções para o maintainer"

---

## Rollback

- Reverter commit do workflow ou desabilitar workflow em Actions
- **Settings → Pages** → remover site ou trocar source
- README pode apontar temporariamente de volta ao conteúdo inline
