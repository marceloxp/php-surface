## Fase 1 — CLI e bootstrap

**Objetivo:** o binário existe, instala dependências e valida entrada básica.

**Entrega:**

* `composer.json` com PHP `^8.1` e autoload PSR-4
* `bin/php-surface` executável
* `--help` e `--version`
* Erro claro quando o arquivo não existe ou não é legível

**Como testar:**

```bash
composer install
./bin/php-surface --help
./bin/php-surface --version
./bin/php-surface arquivo_inexistente.php    # deve falhar com mensagem útil
./bin/php-surface README.md                  # deve falhar (não é .php)
```

**Critérios de aceite:**

- [ ] `composer install` conclui sem erro
- [ ] `--help` lista os comandos/flags previstos na proposta (mesmo que ainda não implementados)
- [ ] `--version` imprime versão
- [ ] Arquivo inexistente → exit code ≠ 0 e mensagem em stderr
- [ ] Arquivo que não é PHP → exit code ≠ 0

**Próxima fase bloqueada até:** os cinco itens acima passarem.
