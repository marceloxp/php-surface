## Fase 4 — Docblocks esparso

**Objetivo:** incluir documentação só onde existe; nunca esqueletos vazios.

**Entrega:**

* `docblock.summary` — primeiro parágrafo útil
* `docblock.return` — texto de `@return`, se houver
* `docblock.throws` — array de `@throws`, se houver
* Chave `docblock` **inteira omitida** quando não há docblock
* Dentro de `docblock`, chaves ausentes quando sem valor (sem `null`)

**Fixture sugerida** (`tests/fixtures/Docblocks.php`):

```php
<?php
namespace App\Svc;

class PaymentGateway
{
    /** Processa pagamento e retorna ID da transação. */
    public function charge(Money $amount): string {}

    public function refund(string $id): void {}

    /**
     * Lista cobranças pendentes.
     *
     * @return array<int, Charge>
     * @throws GatewayException quando o provedor está indisponível
     */
    protected function pending(): array { return []; }
}
```

**Como testar:**

```bash
./bin/php-surface tests/fixtures/Docblocks.php | jq '.symbols[].methods[] | {name, docblock}'
```

**Critérios de aceite:**

- [ ] `charge`: tem `docblock.summary`; **sem** `return`/`throws` se não declarados
- [ ] `refund`: **sem** chave `docblock`
- [ ] `pending`: `summary`, `return` e `throws` presentes com conteúdo correto
- [ ] Nenhum método tem `"summary": null` ou `"throws": []` vazio só para preencher
- [ ] JSON default ainda **sem** `parameters`

**Próxima fase bloqueada até:** todos os itens passarem.