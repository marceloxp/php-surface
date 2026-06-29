<?php

declare(strict_types=1);

namespace App\Demo;

interface Readable
{
    public function read(): string;
}

trait Timestampable
{
    private function now(): int
    {
        return time();
    }
}

class UserRepository implements Readable
{
    public function read(): string
    {
        return '';
    }
}
