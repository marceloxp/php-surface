<?php

declare(strict_types=1);

namespace Tests\Support;

final class PhpSurfaceRunner
{
    private readonly string $root;

    public function __construct(?string $root = null)
    {
        $this->root = $root ?? dirname(__DIR__, 2);
    }

    public function run(array $args, array $env = []): PhpSurfaceResult
    {
        $command = escapeshellarg($this->root . '/bin/php-surface');
        foreach ($args as $arg) {
            $command .= ' ' . escapeshellarg($arg);
        }

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $environment = $env === [] ? null : array_merge($this->baseEnvironment(), $env);

        $process = proc_open($command, $descriptorSpec, $pipes, $this->root, $environment);

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start php-surface process');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return new PhpSurfaceResult(
            proc_close($process),
            $stdout,
            $stderr,
        );
    }

  /**
   * @return array<string, string>
   */
    private function baseEnvironment(): array
    {
        $environment = [];

        foreach ($_SERVER as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $environment[$key] = $value;
            }
        }

        return $environment;
    }
}
