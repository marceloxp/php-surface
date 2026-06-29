<?php

declare(strict_types=1);

namespace PhpSurface\Extractor;

use voku\SimplePhpParser\Model\PHPMethod;
use voku\SimplePhpParser\Model\PHPParameter;

final class ParameterExtractor
{
    /**
     * @return list<array{name: string, type?: string, default?: string}>
     */
    public function extract(PHPMethod $method): array
    {
        $parameters = [];

        foreach ($method->parameters as $parameter) {
            if (!$parameter instanceof PHPParameter) {
                continue;
            }

            $mapped = ['name' => $parameter->name];

            $type = $this->resolveType($parameter);
            if ($type !== null) {
                $mapped['type'] = $type;
            }

            if ($parameter->defaultValue !== null) {
                $mapped['default'] = $this->formatDefaultValue($parameter->defaultValue);
            }

            $parameters[] = $mapped;
        }

        return $parameters;
    }

    private function resolveType(PHPParameter $parameter): ?string
    {
        foreach ([$parameter->type, $parameter->typeFromPhpDocSimple, $parameter->typeFromPhpDoc] as $type) {
            if (!is_string($type) || $type === '') {
                continue;
            }

            $type = ltrim($type, '\\');
            $shortName = strrchr($type, '\\');

            return $shortName === false ? $type : substr($shortName, 1);
        }

        return null;
    }

    private function formatDefaultValue(mixed $value): string
    {
        if (is_string($value)) {
            return var_export($value, true);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return var_export($value, true);
        }

        if ($value === null) {
            return 'null';
        }

        return (string) $value;
    }
}
