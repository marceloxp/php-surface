## Fase 5 — Saída humana (`--text`)

**Objetivo:** mesma informação do mapa, apresentada de forma legível no terminal.

**Entrega:**

* Flag `--text` no lugar do JSON
* Árvore compacta: símbolo → métodos com assinatura (e docblock resumido, se houver)
* Pode repetir informação; omissão esparso é regra só do JSON

**Como testar:**

```bash
./bin/php-surface tests/fixtures/Docblocks.php --text
./bin/php-surface tests/fixtures/Methods.php --text
```

**Critérios de aceite:**

- [ ] Saída não é JSON (stdout legível)
- [ ] Todos os símbolos e métodos das fixtures aparecem
- [ ] Assinaturas conferem com o fonte
- [ ] Método documentado mostra resumo; indocumentado não mostra bloco vazio
- [ ] `--text` sem argumento extra funciona igual com arquivo válido

**Próxima fase bloqueada até:** todos os itens passarem.