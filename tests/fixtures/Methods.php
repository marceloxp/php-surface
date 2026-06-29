<?php

declare(strict_types=1);

namespace App\Repo;

abstract class OrderService
{
    public function save(Order $order): void
    {
    }

    protected static function findActive(int $companyId): array
    {
        return [];
    }

    final public function lock(): bool
    {
        return true;
    }

    abstract protected function resolve(): mixed;
}
