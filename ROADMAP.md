# Roadmap de desenvolvimento — php-surface

Cada fase entrega **uma funcionalidade completa e testável**. O fluxo é:

1. Desenvolver a fase N
2. Você testa factualmente (comandos abaixo)
3. Todos os critérios de aceite passam → avança para a fase N+1
4. Algo falha → corrige na mesma fase antes de seguir

A especificação funcional e o schema JSON estão em [`proposal.md`](proposal.md).

## Fases

As fases estão no diretório `./roadmap`.

* [fase-1.md](fase-1.md)
* [fase-2.md](fase-2.md)
* ...
* [fase-9.md](fase-9.md)
* [fase-10.md](fase-10.md)

## Pós-MVP (não bloqueia entrega)

Funcionalidades **fora do escopo** atual — só após MVP validado:

* índice de repositório inteiro
* resolução cross-file
* call graph / dependências
* LSP

---

## Registro de progresso

| Fase               | Status      | Data | Notas |
| ------------------ | ----------- | ---- | ----- |
| 1 — CLI            | ✅ aprovada | 2026-06-29 |       |
| 2 — Símbolos       | ✅ aprovada | 2026-06-29 |       |
| 3 — Métodos lean   | ✅ aprovada | 2026-06-29 |       |
| 4 — Docblocks      | ✅ aprovada | 2026-06-29 |       |
| 5 — `--text`       | ✅ aprovada | 2026-06-29 |       |
| 6 — `--filter`     | ✅ aprovada | 2026-06-29 |       |
| 7 — `--visibility` | ⬜ pendente |      |       |
| 8 — `--full`       | ⬜ pendente |      |       |
| 9 — `--show`       | ⬜ pendente |      |       |
| 10 — Robustez      | ⬜ pendente |      |       |

Atualize esta tabela ao aprovar cada fase.
