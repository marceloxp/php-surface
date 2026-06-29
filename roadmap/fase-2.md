## Fase 2 — Símbolos (class, trait, interface)

**Objetivo:** dado um arquivo PHP, emitir JSON com os símbolos de nível superior e metadados.

**Entrega:**

* Integração com `voku/simple-php-code-parser`
* JSON com chaves `file` e `symbols[]`
* Por símbolo: `name`, `namespace`, `type` (`class` | `trait` | `interface`), `line`
* `methods` ausente ou array vazio (ainda sem extração de métodos)

**Fixture sugerida** (`tests/fixtures/Symbols.php` — criar na implementação):

```php
<?php
namespace App\Demo;

interface Readable { public function read(): string; }

trait Timestampable { private function now(): int { return time(); } }

class UserRepository implements Readable
{
    public function read(): string { return ''; }
}
```

**Como testar:**

```bash
./bin/php-surface tests/fixtures/Symbols.php | jq .
```

**Critérios de aceite:**

- [ ] JSON válido com `file` e `symbols`
- [ ] Três símbolos: `Readable` (interface), `Timestampable` (trait), `UserRepository` (class)
- [ ] `namespace` = `App\Demo` em todos
- [ ] `line` aponta para a linha real da declaração em cada caso
- [ ] Ordem estável entre execuções (mesma ordem ao rodar duas vezes)
- [ ] Rodar duas vezes produz saída idêntica (sem timestamp, sem ruído)

**Próxima fase bloqueada até:** todos os itens passarem.