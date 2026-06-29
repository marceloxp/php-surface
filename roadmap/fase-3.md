## Fase 3 — Mapa de métodos (JSON lean)

**Objetivo:** cada símbolo lista seus métodos no formato esparso padrão — sem docblock, sem `parameters`.

**Entrega:**

* Por método: `name`, `visibility`, `signature`, `returnType` (omitido se ausente), `startLine`, `endLine`
* `modifiers`: array com `static`, `final`, `abstract` — **omitido quando vazio**
* Sem `docblock`, sem `parameters`, sem booleans `static`/`final`/`abstract`

**Fixture sugerida** (`tests/fixtures/Methods.php`):

```php
<?php
namespace App\Repo;

class OrderService
{
    public function save(Order $order): void {}

    protected static function findActive(int $companyId): array { return []; }

    final public function lock(): bool { return true; }

    abstract protected function resolve(): mixed;
}
```

*(Classe abstrata ou trait com método abstrato — ajustar conforme parser.)*

**Como testar:**

```bash
./bin/php-surface tests/fixtures/Methods.php | jq '.symbols[].methods'
```

**Critérios de aceite:**

- [ ] `save`: `visibility` public, `signature` completa, `returnType` `void`, linhas corretas, **sem** `modifiers`, **sem** `docblock`
- [ ] `findActive`: `modifiers` = `["static"]` apenas (não lista `final`/`abstract` como false)
- [ ] `lock`: `modifiers` contém `final` (e `static` se aplicável)
- [ ] Método sem return type declarado → chave `returnType` **ausente**
- [ ] `startLine`/`endLine` batem com o arquivo fonte (abrir o arquivo e conferir)
- [ ] Nenhum método tem chave `docblock` nem `parameters`
- [ ] Duas execuções → JSON idêntico

**Próxima fase bloqueada até:** todos os itens passarem.