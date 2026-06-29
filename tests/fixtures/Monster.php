<?php

declare(strict_types=1);

namespace Surface\Monster;

use Closure;
use Generator;
use function array_map;
use function count;
use const PHP_VERSION;

// -----------------------------------------------------------------------------
// CASE 001
// Fake attributes for testing
// -----------------------------------------------------------------------------

#[\Attribute(\Attribute::TARGET_ALL)]
class TestAttribute
{
    public function __construct(
        public string $name = '',
        public array $options = [],
        public ?int $priority = null,
    ) {}
}

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET',
        public array $middleware = [],
    ) {}
}

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Inject
{
    public function __construct(
        public ?string $id = null,
    ) {}
}

// -----------------------------------------------------------------------------
// CASE 002
// Interfaces and inheritance
// -----------------------------------------------------------------------------

interface RepositoryInterface
{
    public function find(int $id): ?object;
    public function findAll(): array;
}

interface AdvancedRepositoryInterface extends RepositoryInterface
{
    public function findByCriteria(array $criteria): iterable;
    public function count(): int;
}

interface CachedRepositoryInterface extends AdvancedRepositoryInterface
{
    public function clearCache(): void;
}

// -----------------------------------------------------------------------------
// CASE 003
// Generic interface with PHPDoc
// -----------------------------------------------------------------------------

/**
 * @template T
 * @template V
 */
interface MapperInterface
{
    /**
     * @param T $input
     * @return V
     */
    public function map(mixed $input): mixed;
}

// -----------------------------------------------------------------------------
// CASE 004
// Trait with multiple features
// -----------------------------------------------------------------------------

trait LoggerTrait
{
    private array $logs = [];

    public function log(string $message, string $level = 'info'): void
    {
        $this->logs[] = sprintf('[%s] %s: %s', date('Y-m-d H:i:s'), $level, $message);
    }

    abstract public function getLogs(): array;
}

trait TimestampableTrait
{
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}

trait CacheableTrait
{
    private array $cache = [];

    public function getCache(string $key): mixed
    {
        return $this->cache[$key] ?? null;
    }

    public function setCache(string $key, mixed $value): void
    {
        $this->cache[$key] = $value;
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}

// -----------------------------------------------------------------------------
// CASE 005
// Class using multiple traits with conflicts
// -----------------------------------------------------------------------------

class TraitUser
{
    use LoggerTrait, TimestampableTrait, CacheableTrait {
        LoggerTrait::log as errorLog;
        CacheableTrait::clearCache insteadof TimestampableTrait;
        TimestampableTrait::getCreatedAt as private;
    }

    private array $logStorage = [];

    public function getLogs(): array
    {
        return $this->logStorage;
    }

    public function error(string $message): void
    {
        $this->errorLog($message, 'error');
    }
}

// -----------------------------------------------------------------------------
// CASE 006
// Abstract class with various property types
// -----------------------------------------------------------------------------

abstract class AbstractEntity
{
    #[TestAttribute('id')]
    protected ?int $id = null;

    #[TestAttribute('uuid')]
    private string $uuid;

    #[TestAttribute('created')]
    protected \DateTimeImmutable $createdAt;

    protected array $metadata = [];

    /** @var array<string, mixed> */
    private array $data = [];

    /** @var list<string> */
    private array $tags = [];

    /** @var non-empty-array<string, int> */
    private array $counts = ['default' => 0];

    public function __construct()
    {
        $this->uuid = uniqid();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    abstract public function save(): bool;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}

// -----------------------------------------------------------------------------
// CASE 007
// Enum (PHP 8.1+)
// -----------------------------------------------------------------------------

enum Status: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }
}

enum Color
{
    case RED;
    case GREEN;
    case BLUE;

    public function getHex(): string
    {
        return match ($this) {
            Color::RED => '#FF0000',
            Color::GREEN => '#00FF00',
            Color::BLUE => '#0000FF',
        };
    }
}

// -----------------------------------------------------------------------------
// CASE 008
// Final class with attributes and various constants
// -----------------------------------------------------------------------------

#[TestAttribute('final-class', ['key' => 'value'])]
final class User extends AbstractEntity implements CachedRepositoryInterface, MapperInterface
{
    public const string DEFAULT_NAME = 'Anonymous';
    protected const int MAX_AGE = 150;
    private const array DEFAULT_ROLES = ['user'];

    #[Route('/api/users', 'GET')]
    private string $name;

    #[TestAttribute('email')]
    private string $email;

    #[TestAttribute('age')]
    private ?int $age = null;

    #[TestAttribute('active')]
    private bool $active = true;

    private Status $status = Status::DRAFT;

    private Color $favoriteColor = Color::BLUE;

    private readonly string $hash;

    private static int $instanceCount = 0;

    public function __construct(
        string $name,
        string $email,
        #[Inject('age')] ?int $age = null,
        ?int $id = null,
    ) {
        parent::__construct();

        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->age = $age;
        $this->hash = hash('sha256', $email);

        self::$instanceCount++;
    }

    public function find(int $id): ?object
    {
        return $id === $this->id ? $this : null;
    }

    public function findAll(): array
    {
        return [$this];
    }

    public function findByCriteria(array $criteria): iterable
    {
        $result = [];
        foreach ($criteria as $key => $value) {
            if (property_exists($this, $key) && $this->$key === $value) {
                $result[] = $this;
            }
        }
        return $result;
    }

    public function count(): int
    {
        return 1;
    }

    public function clearCache(): void
    {
        // implementation
    }

    public function map(mixed $input): mixed
    {
        return $input;
    }

    public function save(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'age' => $this->age,
            'active' => $this->active,
            'status' => $this->status->value,
            'hash' => $this->hash,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): void
    {
        $this->age = $age;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    /**
     * @return non-empty-array<string, int>
     */
    public function getCounts(): array
    {
        return $this->counts;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

// -----------------------------------------------------------------------------
// CASE 009
// Class with union and intersection types
// -----------------------------------------------------------------------------

interface LoggerInterface
{
    public function log(string $message): void;
}

interface SerializableInterface
{
    public function serialize(): string;
}

class ComplexTypeExample
{
    // Union Type: Aceita string ou int
    private string|int $id;

    // Intersection Type: O valor deve implementar AMBAS as interfaces
    private LoggerInterface&SerializableInterface $logger;

    /**
     * @param LoggerInterface&SerializableInterface $logger
     */
    public function __construct(
        string|int $id,
        LoggerInterface&SerializableInterface $logger,
        private ?Closure $callback = null
    ) {
        $this->id = $id;
        $this->logger = $logger;
    }

    // Union Type no retorno: Pode retornar string ou int
    public function getId(): string|int
    {
        return $this->id;
    }

    // Método que utiliza a intersecção
    public function processData(): void
    {
        $this->logger->log("Processando ID: {$this->id}");
        $data = $this->logger->serialize();
        // ... lógica adicional
    }
}

// -----------------------------------------------------------------------------
// CASE 010
// Anonymous classes
// -----------------------------------------------------------------------------

class AnonymousClassContainer
{
    public function createAnonymousObject(): object
    {
        return new class implements RepositoryInterface {
            private array $data = [];

            public function find(int $id): ?object
            {
                return $this->data[$id] ?? null;
            }

            public function findAll(): array
            {
                return $this->data;
            }

            public function add(object $item): void
            {
                $this->data[] = $item;
            }
        };
    }

    public function createExtendedAnonymous(): object
    {
        return new class('anonymous') extends AbstractEntity {
            private string $label;

            public function __construct(string $label)
            {
                parent::__construct();
                $this->label = $label;
            }

            public function save(): bool
            {
                return true;
            }

            public function toArray(): array
            {
                return ['label' => $this->label];
            }
        };
    }

    public function createAnonymousWithTrait(): object
    {
        return new class {
            use LoggerTrait;

            public function getLogs(): array
            {
                return $this->logs;
            }
        };
    }
}

// -----------------------------------------------------------------------------
// CASE 011
// Generator methods
// -----------------------------------------------------------------------------

class GeneratorExample
{
    /**
     * Gera uma sequência de números de $start até $end.
     */
    public function generateNumbers(int $start, int $end): Generator
    {
        for ($i = $start; $i <= $end; $i++) {
            yield $i;
        }
    }

    /**
     * Gera pares de chave/valor baseados em um array.
     */
    public function generateKeyValue(): Generator
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        foreach ($data as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * Exemplo de delegação de generator usando yield from.
     */
    public function generateWithYieldFrom(): Generator
    {
        yield from $this->generateNumbers(1, 5);
        yield 'end';
    }

    /**
     * Gera um fluxo infinito de números.
     */
    public function generateInfinite(): Generator
    {
        $i = 0;
        while (true) {
            yield $i++;
        }
    }
}

// -----------------------------------------------------------------------------
// CASE 012
// Closures and arrow functions
// -----------------------------------------------------------------------------

class ClosureExample
{
    public function createClosure(): Closure
    {
        $factor = 2;
        return function (int $x) use ($factor): int {
            return $x * $factor;
        };
    }

    public function createArrowFunction(): Closure
    {
        $factor = 3;
        return fn(int $x): int => $x * $factor;
    }

    public function processArray(array $items): array
    {
        $closure = function (int $item): int {
            return $item * 2;
        };

        $arrow = fn(int $item): int => $item * 3;

        return array_map(fn(int $item): int => $item * 4, $items);
    }

    public function nestedClosure(): Closure
    {
        return function (int $x): Closure {
            return function (int $y) use ($x): int {
                return $x + $y;
            };
        };
    }
}

// -----------------------------------------------------------------------------
// CASE 013
// Method with complex parameters and return types
// -----------------------------------------------------------------------------

class ComplexMethodExample
{
    /**
     * @param string|int $id
     * @param array<string, mixed> $options
     * @param callable(User): bool $filter
     * @return list<User>
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function findUsers(
        string|int $id,
        array $options = [],
        ?callable $filter = null,
        ?User $context = null,
        &$reference = null,
        ...$extra
    ): array {
        if (empty($id)) {
            throw new \InvalidArgumentException('ID cannot be empty');
        }

        $users = [];
        if ($this->someCondition()) {
            throw new \RuntimeException('Something went wrong');
        }

        return $users;
    }

    private function someCondition(): bool
    {
        return false;
    }

    public function neverReturns(): never
    {
        throw new \RuntimeException('This never returns');
    }

    public function voidMethod(): void
    {
        // does nothing
    }

    /**
     * @return Collection<int, User>
     */
    public function getCollection(): iterable
    {
        return new class implements \IteratorAggregate {
            public function getIterator(): \Traversable
            {
                yield 1;
                yield 2;
                yield 3;
            }
        };
    }

    /**
     * @param array<int, User> $users
     * @return list<User>
     */
    public function convertToArray(array $users): array
    {
        return array_values($users);
    }

    /**
     * @param class-string<User> $className
     * @return User
     */
    public function createInstance(string $className): object
    {
        return new $className('default', 'default@example.com');
    }
}

// -----------------------------------------------------------------------------
// CASE 014
// Magic methods
// -----------------------------------------------------------------------------

class MagicMethodsExample
{
    private array $data = [];

    public function __call(string $name, array $arguments): mixed
    {
        if (str_starts_with($name, 'get')) {
            $property = lcfirst(substr($name, 3));
            return $this->data[$property] ?? null;
        }

        if (str_starts_with($name, 'set')) {
            $property = lcfirst(substr($name, 3));
            $this->data[$property] = $arguments[0] ?? null;
            return $this;
        }

        throw new \BadMethodCallException("Method $name does not exist");
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function __invoke(...$args): mixed
    {
        return count($args);
    }

    public function __debugInfo(): array
    {
        return ['data' => $this->data];
    }

    public function __clone(): void
    {
        $this->data = array_map(fn($item) => is_object($item) ? clone $item : $item, $this->data);
    }

    public function __sleep(): array
    {
        return ['data'];
    }

    public function __wakeup(): void
    {
        // initialize resources
    }
}

// -----------------------------------------------------------------------------
// CASE 015
// Large method for line count testing (~100 lines)
// -----------------------------------------------------------------------------

class LargeMethodExample
{
    public function largeMethodWithComplexControlFlow(
        array $data,
        ?array $filters = null,
        string $sortBy = 'id',
        string $sortOrder = 'ASC',
        int $limit = 100,
        int $offset = 0
    ): array {
        // Start with input validation
        if (empty($data)) {
            return [];
        }

        if (!is_array($data) || count($data) === 0) {
            return [];
        }

        // Apply filters
        if ($filters !== null && count($filters) > 0) {
            foreach ($filters as $key => $value) {
                if (isset($value['operator'])) {
                    switch ($value['operator']) {
                        case 'eq':
                            $data = array_filter($data, fn($item) => $item[$key] === $value['value']);
                            break;
                        case 'gt':
                            $data = array_filter($data, fn($item) => $item[$key] > $value['value']);
                            break;
                        case 'lt':
                            $data = array_filter($data, fn($item) => $item[$key] < $value['value']);
                            break;
                        case 'gte':
                            $data = array_filter($data, fn($item) => $item[$key] >= $value['value']);
                            break;
                        case 'lte':
                            $data = array_filter($data, fn($item) => $item[$key] <= $value['value']);
                            break;
                        case 'contains':
                            $data = array_filter($data, fn($item) => str_contains($item[$key], $value['value']));
                            break;
                        default:
                            // skip unknown operator
                            break;
                    }
                } else {
                    // Simple equality filter
                    $data = array_filter($data, fn($item) => ($item[$key] ?? null) === $value);
                }
            }
        }

        // Sort the data
        if (!empty($sortBy)) {
            $sortOrderMultiplier = strtoupper($sortOrder) === 'DESC' ? -1 : 1;
            usort($data, function ($a, $b) use ($sortBy, $sortOrderMultiplier) {
                $aVal = $a[$sortBy] ?? 0;
                $bVal = $b[$sortBy] ?? 0;
                if ($aVal === $bVal) {
                    return 0;
                }
                return ($aVal < $bVal ? -1 : 1) * $sortOrderMultiplier;
            });
        }

        // Apply limit and offset
        if ($limit > 0) {
            $data = array_slice($data, $offset, $limit);
        } elseif ($offset > 0) {
            $data = array_slice($data, $offset);
        }

        // Transform each item
        $result = [];
        foreach ($data as $index => $item) {
            try {
                if (!is_array($item)) {
                    throw new \InvalidArgumentException("Item at index $index is not an array");
                }

                $transformed = [];
                foreach ($item as $key => $value) {
                    if (is_string($value)) {
                        $transformed[$key] = trim($value);
                    } elseif (is_int($value)) {
                        $transformed[$key] = $value;
                    } elseif (is_float($value)) {
                        $transformed[$key] = round($value, 2);
                    } elseif (is_bool($value)) {
                        $transformed[$key] = $value ? 1 : 0;
                    } elseif ($value === null) {
                        $transformed[$key] = null;
                    } elseif (is_array($value)) {
                        $transformed[$key] = json_encode($value);
                    } else {
                        $transformed[$key] = (string) $value;
                    }
                }

                // Add metadata
                $transformed['_index'] = $index;
                $transformed['_timestamp'] = time();

                $result[] = $transformed;
            } catch (\Exception $e) {
                // Log error and continue
                continue;
            }
        }

        // Apply post-processing
        if (count($result) > 0) {
            $lastItem = &$result[count($result) - 1];
            $lastItem['_last'] = true;
            unset($lastItem);
        }

        return $result;
    }
}

// -----------------------------------------------------------------------------
// CASE 016
// Another large method (~200 lines) for line count testing
// -----------------------------------------------------------------------------

class HugeMethodExample
{
    /**
     * @param array<string, mixed> $config
     * @param list<string> $requiredFields
     * @return array<string, mixed>
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function processConfiguration(
        array $config,
        array $requiredFields = [],
        array $defaults = [],
        ?callable $validator = null,
        bool $strict = true
    ): array {
        $result = [];
        $errors = [];
        $warnings = [];

        // Validate configuration structure
        if (!is_array($config)) {
            throw new \InvalidArgumentException('Configuration must be an array');
        }

        // Apply defaults
        foreach ($defaults as $key => $defaultValue) {
            if (!array_key_exists($key, $config)) {
                $config[$key] = $defaultValue;
                $warnings[] = "Using default value for '$key'";
            }
        }

        // Check required fields
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $config) || $config[$field] === null) {
                $errors[] = "Required field '$field' is missing or null";
            }
        }

        if (!empty($errors) && $strict) {
            throw new \InvalidArgumentException('Configuration validation failed: ' . implode(', ', $errors));
        }

        // Process each configuration key
        foreach ($config as $key => $value) {
            try {
                // Skip invalid keys
                if (!is_string($key)) {
                    $errors[] = "Configuration key must be string, got " . gettype($key);
                    continue;
                }

                // Validate value based on key
                switch ($key) {
                    case 'host':
                    case 'hostname':
                        if (!is_string($value)) {
                            throw new \InvalidArgumentException("'$key' must be a string");
                        }
                        if (empty($value)) {
                            throw new \InvalidArgumentException("'$key' cannot be empty");
                        }
                        $result[$key] = $value;
                        break;

                    case 'port':
                        if (!is_int($value) && !is_numeric($value)) {
                            throw new \InvalidArgumentException("'$key' must be an integer");
                        }
                        $port = (int) $value;
                        if ($port < 1 || $port > 65535) {
                            throw new \InvalidArgumentException("'$key' must be between 1 and 65535");
                        }
                        $result[$key] = $port;
                        break;

                    case 'timeout':
                    case 'lifetime':
                        if (!is_int($value) && !is_numeric($value)) {
                            throw new \InvalidArgumentException("'$key' must be a number");
                        }
                        $result[$key] = (int) $value;
                        break;

                    case 'enabled':
                    case 'debug':
                    case 'development':
                        $result[$key] = (bool) $value;
                        break;

                    case 'options':
                        if (!is_array($value)) {
                            throw new \InvalidArgumentException("'$key' must be an array");
                        }
                        $processed = [];
                        foreach ($value as $optKey => $optValue) {
                            if (is_string($optKey)) {
                                $processed[$optKey] = is_string($optValue) ? trim($optValue) : $optValue;
                            }
                        }
                        $result[$key] = $processed;
                        break;

                    case 'url':
                    case 'endpoint':
                        if (!is_string($value)) {
                            throw new \InvalidArgumentException("'$key' must be a string");
                        }
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            throw new \InvalidArgumentException("'$key' must be a valid URL");
                        }
                        $result[$key] = $value;
                        break;

                    case 'regex':
                        if (!is_string($value)) {
                            throw new \InvalidArgumentException("'$key' must be a string");
                        }
                        if (@preg_match($value, '') === false) {
                            throw new \InvalidArgumentException("'$key' must be a valid regular expression");
                        }
                        $result[$key] = $value;
                        break;

                    default:
                        // Unknown key, pass through if valid
                        if (is_scalar($value) || is_array($value) || $value === null) {
                            if (is_string($value)) {
                                $result[$key] = trim($value);
                            } elseif (is_bool($value)) {
                                $result[$key] = $value;
                            } elseif (is_numeric($value)) {
                                $result[$key] = $value;
                            } elseif (is_array($value)) {
                                $result[$key] = $value;
                            } else {
                                $result[$key] = $value;
                            }
                        } else {
                            throw new \InvalidArgumentException("'$key' has unsupported type: " . gettype($value));
                        }
                        break;
                }
            } catch (\InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
                if ($strict) {
                    throw $e;
                }
            } catch (\Exception $e) {
                $errors[] = "Unexpected error processing '$key': " . $e->getMessage();
                if ($strict) {
                    throw new \RuntimeException("Configuration processing failed: " . $e->getMessage(), 0, $e);
                }
            }
        }

        // Apply custom validator if provided
        if ($validator !== null) {
            $validationResult = $validator($result);
            if ($validationResult === false) {
                $errors[] = "Custom validation failed";
                if ($strict) {
                    throw new \InvalidArgumentException("Custom validation failed");
                }
            } elseif (is_string($validationResult)) {
                $errors[] = $validationResult;
                if ($strict) {
                    throw new \InvalidArgumentException($validationResult);
                }
            }
        }

        // Merge errors and warnings into result
        if (!empty($errors)) {
            $result['_errors'] = $errors;
        }
        if (!empty($warnings)) {
            $result['_warnings'] = $warnings;
        }
        $result['_valid'] = empty($errors);

        return $result;
    }

    /**
     * Third large method for line count testing (~150 lines)
     */
    public function processDataWithLoops(array $items, array $config = []): array
    {
        $result = [];
        $total = 0;
        $count = 0;
        $errors = [];

        // Process each item with nested loops
        foreach ($items as $index => $item) {
            try {
                if (!is_array($item)) {
                    throw new \InvalidArgumentException("Item $index is not an array");
                }

                $processed = [];
                $subtotal = 0;

                // Process sub-items
                if (isset($item['children']) && is_array($item['children'])) {
                    foreach ($item['children'] as $childIndex => $child) {
                        if (!is_array($child)) {
                            continue;
                        }

                        $childProcessed = [];
                        foreach ($child as $key => $value) {
                            if (is_string($value)) {
                                $childProcessed[$key] = strtoupper($value);
                                $subtotal += strlen($value);
                            } elseif (is_numeric($value)) {
                                $childProcessed[$key] = $value * 2;
                                $subtotal += $value;
                            } elseif (is_bool($value)) {
                                $childProcessed[$key] = $value;
                            } else {
                                $childProcessed[$key] = $value;
                            }
                        }
                        $processed['children'][] = $childProcessed;
                        $count++;
                    }
                }

                // Process main item
                foreach ($item as $key => $value) {
                    if ($key === 'children') {
                        continue;
                    }

                    if (is_string($value)) {
                        $processed[$key] = $value;
                        $total += strlen($value);
                    } elseif (is_int($value)) {
                        $processed[$key] = $value * 2;
                        $total += $value;
                    } elseif (is_float($value)) {
                        $processed[$key] = round($value, 4);
                        $total += $value;
                    } elseif (is_bool($value)) {
                        $processed[$key] = $value;
                    } elseif (is_array($value)) {
                        $processed[$key] = array_values($value);
                        $total += count($value);
                    } else {
                        $processed[$key] = $value;
                    }
                }

                $processed['_index'] = $index;
                $processed['_subtotal'] = $subtotal;
                $result[] = $processed;
            } catch (\InvalidArgumentException $e) {
                $errors[] = "Error processing item $index: " . $e->getMessage();
                if ($config['strict'] ?? false) {
                    throw $e;
                }
                continue;
            } catch (\Exception $e) {
                $errors[] = "Unexpected error processing item $index: " . $e->getMessage();
                continue;
            }
        }

        // Add summary
        $result['_summary'] = [
            'total_items' => count($result),
            'total_value' => $total,
            'count' => $count,
            'errors' => $errors,
            'timestamp' => time(),
        ];

        return $result;
    }
}

// -----------------------------------------------------------------------------
// CASE 017
// Short methods for line count testing
// -----------------------------------------------------------------------------

class ShortMethodExample
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    public function multiply(int $a, int $b): int
    {
        return $a * $b;
    }

    public function isEven(int $n): bool
    {
        return $n % 2 === 0;
    }

    public function getStatus(): string
    {
        return 'active';
    }

    public function clear(): void
    {
        // no-op
    }

    public function getDefault(): ?string
    {
        return null;
    }

    public function getList(): array
    {
        return [];
    }

    public function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }
}

// -----------------------------------------------------------------------------
// CASE 018
// Various control flow structures
// -----------------------------------------------------------------------------

class ControlFlowExample
{
    private array $data = [];

    public function demonstrateControlFlow(int $value, array $items): void
    {
        // if/elseif/else
        if ($value === 0) {
            $this->data['zero'] = true;
        } elseif ($value > 0 && $value < 10) {
            $this->data['small'] = true;
        } elseif ($value >= 10 && $value < 100) {
            $this->data['medium'] = true;
        } else {
            $this->data['large'] = true;
        }

        // switch
        switch ($value) {
            case 1:
            case 2:
                $this->data['one_or_two'] = true;
                break;
            case 3:
                $this->data['three'] = true;
                // fall-through
            case 4:
                $this->data['three_or_four'] = true;
                break;
            default:
                $this->data['other'] = true;
        }

        // match (PHP 8+)
        $this->data['match'] = match ($value) {
            0 => 'zero',
            1, 2 => 'one_or_two',
            3, 4, 5 => 'three_to_five',
            default => 'other',
        };

        // while
        $i = 0;
        while ($i < count($items)) {
            $this->data['while'][] = $items[$i];
            $i++;
        }

        // do/while
        $j = 0;
        do {
            $this->data['dowhile'][] = $j;
            $j++;
        } while ($j < 5);

        // for
        for ($k = 0; $k < 10; $k++) {
            if ($k % 2 === 0) {
                continue;
            }
            if ($k > 8) {
                break;
            }
            $this->data['for'][] = $k;
        }

        // foreach
        foreach ($items as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->data['foreach'][$key] = $value;
        }

        // foreach with reference
        foreach ($items as &$item) {
            $item = $item * 2;
        }
        unset($item);

        // goto (use sparingly)
        if ($value === 42) {
            goto found_answer;
        }
        $this->data['not_answer'] = true;
        found_answer:
        $this->data['answer'] = 42;

        // try/catch/finally
        try {
            if ($value === 0) {
                throw new \Exception('Value is zero');
            }
            $this->data['try_success'] = true;
        } catch (\InvalidArgumentException $e) {
            $this->data['catch_invalid'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->data['catch_generic'] = $e->getMessage();
        } finally {
            $this->data['finally'] = true;
        }

        // try with multiple catches and finally
        try {
            if ($value < 0) {
                throw new \RuntimeException('Negative value');
            }
            if ($value > 1000) {
                throw new \OverflowException('Value too large');
            }
        } catch (\OverflowException | \RuntimeException $e) {
            $this->data['catch_union'] = $e->getMessage();
        } catch (\Throwable $e) {
            $this->data['catch_throwable'] = $e->getMessage();
        }
    }
}

// -----------------------------------------------------------------------------
// CASE 019
// Expressions and operators
// -----------------------------------------------------------------------------

class ExpressionsExample
{
    private array $data = [];
    private ?string $name = null;

    public function demonstrateExpressions(): void
    {
        // Null coalescing
        $this->data['name'] = $this->name ?? 'default';

        // Null coalescing assignment
        $this->data['value'] ??= 42;

        // Nullsafe operator
        $this->data['length'] = $this->getName()?->getLength() ?? 0;

        // Ternary operator
        $this->data['status'] = isset($this->data['value']) ? 'set' : 'unset';

        // Ternary with null coalescing
        $this->data['result'] = isset($this->data['value']) ? $this->data['value'] : 'default';

        // Spaceship operator
        $this->data['comparison'] = $this->data['a'] ?? 0 <=> $this->data['b'] ?? 0;

        // Logical operators
        $this->data['and'] = $this->data['a'] && $this->data['b'];
        $this->data['or'] = $this->data['a'] || $this->data['b'];
        $this->data['xor'] = $this->data['a'] xor $this->data['b'];
        $this->data['not'] = !$this->data['a'];

        // Bitwise operators
        $this->data['bitwise_and'] = $this->data['a'] & $this->data['b'];
        $this->data['bitwise_or'] = $this->data['a'] | $this->data['b'];
        $this->data['bitwise_xor'] = $this->data['a'] ^ $this->data['b'];
        $this->data['bitwise_not'] = ~$this->data['a'];
        $this->data['bitwise_shift'] = $this->data['a'] << 2;

        // Combined assignments
        $this->data['a'] += 10;
        $this->data['b'] -= 5;
        $this->data['c'] *= 2;
        $this->data['d'] /= 3;
        $this->data['e'] %= 4;
        $this->data['f'] **= 2;
        $this->data['g'] |= 0x0F;
        $this->data['h'] &= 0xFF;
        $this->data['i'] ^= 0xAA;

        // Increment/decrement
        $this->data['counter']++;
        $this->data['counter']--;
        ++$this->data['counter'];
        --$this->data['counter'];

        // Array destructuring
        [$a, $b] = [1, 2];
        ['a' => $c, 'b' => $d] = ['a' => 3, 'b' => 4];
        [$x,, $z] = [5, 6, 7];

        $temp = [8];
        [&$ref] = $temp;
        $ref = 9;
    }

    private function getName(): ?self
    {
        return $this;
    }

    private function getLength(): int
    {
        return 5;
    }
}

// -----------------------------------------------------------------------------
// CASE 020
// Named arguments and attributes with parameters
// -----------------------------------------------------------------------------

class NamedArgumentsExample
{
    #[TestAttribute(
        name: 'example',
        options: ['key' => 'value', 'count' => 42],
        priority: 100
    )]
    public function namedArgumentsExample(
        #[Inject(id: 'service')]
        object $service,
        array $config = [],
        int $limit = 10,
    ): void {
        // Using named arguments in function call
        $this->processData(
            data: $config,
            limit: $limit,
            offset: 0,
        );
    }

    private function processData(array $data, int $limit, int $offset): void
    {
        // implementation
    }

    #[Route(path: '/api/example', method: 'POST', middleware: ['auth', 'csrf'])]
    #[TestAttribute('multiple', ['a' => 1, 'b' => 2])]
    public function multipleAttributes(): void
    {
        // implementation
    }
}

// -----------------------------------------------------------------------------
// CASE 021
// Static properties and methods
// -----------------------------------------------------------------------------

class StaticExample
{
    private static array $instances = [];
    private static int $counter = 0;
    private static ?string $defaultName = null;

    public static function create(): self
    {
        self::$counter++;
        return new self();
    }

    public static function getInstances(): array
    {
        return self::$instances;
    }

    public static function setDefaultName(?string $name): void
    {
        self::$defaultName = $name;
    }

    public static function getDefaultName(): ?string
    {
        return self::$defaultName;
    }

    public static function reset(): void
    {
        self::$instances = [];
        self::$counter = 0;
    }
}

// -----------------------------------------------------------------------------
// CASE 022
// Final methods and abstract methods
// -----------------------------------------------------------------------------

abstract class AbstractWithFinal
{
    abstract protected function doSomething(): void;

    final public function finalMethod(): void
    {
        // This is inherited as-is
    }
}

final class FinalClass extends AbstractWithFinal
{
    protected function doSomething(): void
    {
        // Implementation is required here
    }
}

// -----------------------------------------------------------------------------
// CASE 023
// Multiple inheritance-like constructs with interfaces and traits
// -----------------------------------------------------------------------------

interface A
{
    public function methodA(): void;
}

interface B extends A
{
    public function methodB(): void;
}

interface C
{
    public function methodC(): void;
}

trait TraitA
{
    public function methodA(): void
    {
        // implementation
    }
}

trait TraitB
{
    public function methodB(): void
    {
        // implementation
    }
}

trait TraitC
{
    use TraitA;

    public function methodC(): void
    {
        // implementation
    }
}

class MultipleInheritance implements B, C
{
    use TraitA, TraitB, TraitC {
        TraitA::methodA insteadof TraitC;
        TraitB::methodB as private;
    }

    public function methodA(): void
    {
        // Override
    }

    public function methodC(): void
    {
        // Override
    }
}

// -----------------------------------------------------------------------------
// CASE 024
// Type declarations with arrays and mixed
// -----------------------------------------------------------------------------

class TypeDeclarationExample
{
    private array $list = [];
    private mixed $mixedValue = null;
    private bool $flag = false;
    private int $integer = 0;
    private float $float = 0.0;
    private string $string = '';
    private object $object;
    private iterable $iterable = [];
    private ?int $nullable = null;
    private ?string $nullableString = null;
    private ?\Closure $nullableClosure = null;

    public function __construct()
    {
        $this->object = new \stdClass();
    }

    public function process(array $items): array
    {
        return $items;
    }

    public function processMixed(mixed $input): mixed
    {
        if (is_array($input)) {
            return $this->process($input);
        }
        return $input;
    }

    public function processIterable(iterable $input): iterable
    {
        foreach ($input as $item) {
            yield $item;
        }
    }

    public function processNullable(?int $number): ?int
    {
        return $number === null ? null : $number * 2;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function setObject(object $object): void
    {
        $this->object = $object;
    }

    public function getClosure(): ?\Closure
    {
        return $this->nullableClosure;
    }

    public function setClosure(\Closure $closure): void
    {
        $this->nullableClosure = $closure;
    }
}

// -----------------------------------------------------------------------------
// CASE 025
// Constructor promotion and readonly
// -----------------------------------------------------------------------------

class ConstructorPromotionExample
{
    public function __construct(
        public string $name,
        #[TestAttribute('email')]
        public readonly string $email,
        protected ?int $age = null,
        private array $options = [],
        readonly private string $hash = '',
        public readonly bool $active = true,
        public ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->hash = hash('sha256', $this->email);
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}

// -----------------------------------------------------------------------------
// CASE 026
// Constants with visibility and attributes
// -----------------------------------------------------------------------------

class ConstantsExample
{
    public const string PUBLIC_CONST = 'public';
    protected const string PROTECTED_CONST = 'protected';
    private const string PRIVATE_CONST = 'private';

    public const array COMPLEX_CONST = [
        'key1' => 'value1',
        'key2' => 'value2',
        'nested' => ['a', 'b', 'c'],
    ];

    public const int INTEGER_CONST = 42;
    public const float FLOAT_CONST = 3.14;
    public const bool BOOL_CONST = true;
    public const ?string NULLABLE_CONST = null;

    #[TestAttribute('constant')]
    public const string ATTRIBUTED_CONST = 'attributed';

    public const string HASH_CONST = __CLASS__ . '#' . __FUNCTION__;

    public function useConstants(): void
    {
        $value = self::PUBLIC_CONST;
        $value = self::COMPLEX_CONST['key1'];
        $value = self::INTEGER_CONST;
        $value = self::FLOAT_CONST;
        $value = self::BOOL_CONST;
    }
}

// -----------------------------------------------------------------------------
// CASE 027
// Class with nested anonymous classes and closures
// -----------------------------------------------------------------------------

class NestedConstructsExample
{
    public function complexMethod(): array
    {
        $result = [];

        // Anonymous class inside method
        $handler = new class implements RepositoryInterface {
            private array $data = [];

            public function find(int $id): ?object
            {
                return $this->data[$id] ?? null;
            }

            public function findAll(): array
            {
                return $this->data;
            }

            public function add(int $id, object $item): void
            {
                $this->data[$id] = $item;
            }
        };

        $result['handler'] = $handler;

        // Closure with use
        $factor = 2;
        $closure = function (int $x) use ($factor, $handler): int {
            $y = $x * $factor;
            $handler->add($y, new \stdClass());
            return $y;
        };
        $result['closure'] = $closure;

        // Arrow function with use
        $multiplier = 3;
        $arrow = fn(int $x) => $x * $multiplier;
        $result['arrow'] = $arrow;

        // Nested closure
        $nested = function (int $x) use ($factor, $multiplier): Closure {
            return function (int $y) use ($x, $factor, $multiplier): int {
                return ($x + $y) * $factor * $multiplier;
            };
        };
        $result['nested'] = $nested;

        // Closure with self and parent
        $selfClosure = function (): self {
            return $this;
        };
        $result['self'] = $selfClosure;

        // Closure with static
        $staticClosure = static function (int $x): int {
            return $x * 2;
        };
        $result['static'] = $staticClosure;

        return $result;
    }

    public function getFactory(): Closure
    {
        return function (string $type): object {
            return match ($type) {
                'user' => new User('factory', 'factory@example.com'),
                'guest' => new class {
                    public string $type = 'guest';
                    public function getName(): string
                    {
                        return 'Guest';
                    }
                },
                default => new \stdClass(),
            };
        };
    }
}

// -----------------------------------------------------------------------------
// CASE 028
// PHPDoc with generic syntax and complex types
// -----------------------------------------------------------------------------

/**
 * Generic collection class
 *
 * @template TKey
 * @template TValue
 * @implements \IteratorAggregate<TKey, TValue>
 */
class GenericCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array<TKey, TValue>
     */
    private array $items = [];

    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function set(mixed $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * @param TKey $key
     * @return TValue|null
     */
    public function get(mixed $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    /**
     * @return \Traversable<TKey, TValue>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param callable(TValue, TKey): bool $callback
     * @return self<TKey, TValue>
     */
    public function filter(callable $callback): self
    {
        $filtered = array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH);
        return new self($filtered);
    }

    /**
     * @template TNewValue
     * @param callable(TValue, TKey): TNewValue $callback
     * @return self<TKey, TNewValue>
     */
    public function map(callable $callback): self
    {
        $mapped = [];
        foreach ($this->items as $key => $value) {
            $mapped[$key] = $callback($value, $key);
        }
        return new self($mapped);
    }

    /**
     * @return list<TValue>
     */
    public function values(): array
    {
        return array_values($this->items);
    }

    /**
     * @return list<TKey>
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /**
     * @param TValue $value
     * @return bool
     */
    public function contains(mixed $value): bool
    {
        return in_array($value, $this->items, true);
    }

    /**
     * @param TKey $key
     * @return bool
     */
    public function has(mixed $key): bool
    {
        return array_key_exists($key, $this->items);
    }
}

// -----------------------------------------------------------------------------
// CASE 029
// Type aliases and complex PHPDoc
// -----------------------------------------------------------------------------

/**
 * @template T
 * @typedef array{
 *     id: int,
 *     name: string,
 *     email: string,
 *     status: Status,
 *     metadata: array<string, mixed>,
 *     created_at: \DateTimeImmutable,
 *     updated_at: \DateTimeImmutable|null
 * } UserData
 */

/**
 * @template TKey of array-key
 * @template TValue
 * @template TDefault
 */
interface RepositoryWithDefaults
{
    /**
     * @param TKey $id
     * @param TDefault $default
     * @return TValue|TDefault
     */
    public function findOrDefault(mixed $id, mixed $default): mixed;

    /**
     * @param callable(TKey, TValue): bool $predicate
     * @return self<TKey, TValue>
     */
    public function where(callable $predicate): self;
}

// -----------------------------------------------------------------------------
// CASE 030
// Methods with by-reference parameters and return
// -----------------------------------------------------------------------------

class ReferenceExample
{
    private array $data = [];

    public function &getByReference(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function setValue(string $key, &$value): void
    {
        $this->data[$key] = &$value;
    }

    public function increment(&$value): void
    {
        $value++;
    }

    public function swap(&$a, &$b): void
    {
        $temp = $a;
        $a = $b;
        $b = $temp;
    }

    public function processReferences(array &$array, &$result): void
    {
        $result = [];
        foreach ($array as &$item) {
            $item = $item * 2;
            $result[] = &$item;
        }
        unset($item);
    }

    public function getData(): array
    {
        return $this->data;
    }
}

// -----------------------------------------------------------------------------
// CASE 031
// Variadic parameters and unpacking
// -----------------------------------------------------------------------------

class VariadicExample
{
    public function sum(int ...$numbers): int
    {
        return array_sum($numbers);
    }

    public function concat(string ...$parts): string
    {
        return implode('', $parts);
    }

    public function merge(array ...$arrays): array
    {
        return array_merge(...$arrays);
    }

    public function combine(string $prefix, string ...$items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $prefix . $item;
        }
        return $result;
    }

    public function processVariadic(
        string $name,
        int $id,
        ...$options
    ): array {
        return [
            'name' => $name,
            'id' => $id,
            'options' => $options,
        ];
    }

    public function callVariadic(): void
    {
        $numbers = [1, 2, 3, 4, 5];
        $sum = $this->sum(...$numbers);

        $items = ['a', 'b', 'c'];
        $this->concat(...$items);

        $arrays = [[1, 2], [3, 4], [5, 6]];
        $merged = $this->merge(...$arrays);
    }
}

// -----------------------------------------------------------------------------
// CASE 032
// Throw expressions and match expressions
// -----------------------------------------------------------------------------

class ThrowExpressionExample
{
    public function processValue($value): int
    {
        return match ($value) {
            null => throw new \InvalidArgumentException('Value cannot be null'),
            0, 1, 2 => $value * 2,
            default => throw new \RuntimeException('Invalid value: ' . $value),
        };
    }

    public function getValue($input): int
    {
        return $input ?? throw new \InvalidArgumentException('Input required');
    }

    public function processArray(array $data): array
    {
        return array_map(
            fn($value) => $value === null ? throw new \InvalidArgumentException('Null value') : $value,
            $data
        );
    }
}

// -----------------------------------------------------------------------------
// CASE 033
// Return types with self, parent, and static
// -----------------------------------------------------------------------------

class BaseClass {}

// 2. A classe estende a base para tornar 'parent' válido
class ReturnTypeExample extends BaseClass
{
    public function getSelf(): self
    {
        return $this;
    }

    public function getStatic(): static
    {
        return new static();
    }

    public function getParent(): parent
    {
        // O parser agora aceita 'parent' pois há uma classe pai definida
        return new parent();
    }

    public function getMixed(): mixed
    {
        return $this;
    }

    public function getNever(): never
    {
        throw new \RuntimeException('Never returns');
    }

    public function getVoid(): void
    {
        return;
    }

    public function getGenerator(): Generator
    {
        yield 1;
    }

    public function getIterable(): iterable
    {
        return [1, 2, 3];
    }
}

// -----------------------------------------------------------------------------
// CASE 034
// Attributes with constants and arrays
// -----------------------------------------------------------------------------

#[TestAttribute(
    name: ConstantsExample::PUBLIC_CONST,
    options: ConstantsExample::COMPLEX_CONST,
    priority: ConstantsExample::INTEGER_CONST
)]
class AttributeWithConstants
{
    #[Route(
        path: '/api/' . ConstantsExample::PUBLIC_CONST,
        method: 'POST',
        middleware: ['auth', ConstantsExample::PUBLIC_CONST]
    )]
    public function attributedMethod(): void
    {
        // implementation
    }

    #[TestAttribute(
        name: 'complex',
        options: [
            'a' => 1,
            'b' => ConstantsExample::INTEGER_CONST,
            'c' => __DIR__,
            'd' => __FILE__,
            'e' => __LINE__,
            'f' => __CLASS__,
            'g' => __METHOD__,
            'h' => __FUNCTION__,
            'i' => __NAMESPACE__,
        ]
    )]
    public function complexAttribute(): void
    {
        // implementation
    }
}

// -----------------------------------------------------------------------------
// CASE 035
// Final constants and constants in interfaces
// -----------------------------------------------------------------------------

interface ConstantInterface
{
    public const string INTERFACE_CONST = 'interface_const';
    public const int MAX_SIZE = 100;
    public const array DEFAULT_CONFIG = ['timeout' => 30];
}

class ImplementsConstants implements ConstantInterface
{
    public function getConstant(): string
    {
        return self::INTERFACE_CONST;
    }

    public function getMaxSize(): int
    {
        return self::MAX_SIZE;
    }
}

// -----------------------------------------------------------------------------
// CASE 036
// Dynamic properties (with allowed and disallowed)
// -----------------------------------------------------------------------------

class DynamicPropertiesExample
{
    private array $data = [];

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function getData(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}

// -----------------------------------------------------------------------------
// CASE 037
// Abstract class with abstract constants
// -----------------------------------------------------------------------------

abstract class AbstractWithConstants
{
    // Enforce that child classes must implement these methods
    abstract public function getName(): string;
    abstract public function getVersion(): int;
}

class ConcreteMonster extends AbstractWithConstants
{
    public function getName(): string
    {
        return 'MonsterName';
    }

    public function getVersion(): int
    {
        return 1;
    }
}

// -----------------------------------------------------------------------------
// CASE 038
// More trait examples with property types
// -----------------------------------------------------------------------------

trait PropertyTrait
{
    public string $publicProperty = 'default';
    protected int $protectedProperty = 0;
    private array $privateProperty = [];

    public function getPublicProperty(): string
    {
        return $this->publicProperty;
    }

    public function setPublicProperty(string $value): void
    {
        $this->publicProperty = $value;
    }

    protected function getProtectedProperty(): int
    {
        return $this->protectedProperty;
    }

    protected function setProtectedProperty(int $value): void
    {
        $this->protectedProperty = $value;
    }

    private function getPrivateProperty(): array
    {
        return $this->privateProperty;
    }

    private function setPrivateProperty(array $value): void
    {
        $this->privateProperty = $value;
    }
}

trait StaticPropertyTrait
{
    private static string $staticProperty = 'static';

    public static function getStaticProperty(): string
    {
        return self::$staticProperty;
    }

    public static function setStaticProperty(string $value): void
    {
        self::$staticProperty = $value;
    }
}

class TraitPropertyUser
{
    use PropertyTrait, StaticPropertyTrait;

    public function test(): void
    {
        $this->publicProperty = 'test';
        $this->setProtectedProperty(42);
        // Cannot access private property directly
        $this->setPrivateProperty(['test' => 'value']);

        self::setStaticProperty('new value');
        $value = self::getStaticProperty();
    }
}

// -----------------------------------------------------------------------------
// CASE 039
// Methods with mixed parameter and return types
// -----------------------------------------------------------------------------

class MixedTypesExample
{
    public function process(mixed $input): mixed
    {
        if (is_string($input)) {
            return strtoupper($input);
        }
        if (is_int($input)) {
            return $input * 2;
        }
        if (is_array($input)) {
            return array_map(fn($item) => $this->process($item), $input);
        }
        return $input;
    }

    public function handle(mixed ...$args): mixed
    {
        if (count($args) === 1) {
            return $this->process($args[0]);
        }
        return array_map(fn($arg) => $this->process($arg), $args);
    }

    public function getMixedOrNull(): mixed
    {
        return rand(0, 1) ? 42 : null;
    }

    public function acceptMixedArray(array $items): mixed
    {
        return $items[array_rand($items)] ?? null;
    }
}

// -----------------------------------------------------------------------------
// CASE 040
// Attribute with named arguments and array unpacking
// -----------------------------------------------------------------------------

#[TestAttribute(
    name: 'unpacked',
    options: [...ConstantsExample::COMPLEX_CONST],
    priority: 5
)]
class AttributeUnpackExample
{
    private array $data = [];

    #[Route(
        path: '/api/example',
        method: 'POST',
        middleware: [...['auth', 'csrf'], 'cors']
    )]
    public function handle(): void
    {
        $config = [...ConstantsExample::COMPLEX_CONST, 'extra' => 'value'];
        $this->data = $config;
    }
}

// -----------------------------------------------------------------------------
// CASE 041
// Class with all types of PHPDoc tags
// -----------------------------------------------------------------------------

/**
 * Comprehensive PHPDoc Example Class
 *
 * This class demonstrates all major PHPDoc tags and their usage.
 * It serves as a test case for parser edge cases.
 *
 * @package Surface\Monster
 * @author PHP-Surface
 * @version 1.0.0
 * @deprecated Use NewClass instead
 * @internal
 * @see NewClass
 * @link https://example.com/docs
 * @since 1.0.0
 * @category Testing
 * @license MIT
 * @copyright 2024
 * @uses User
 * @uses RepositoryInterface
 * @template T
 */
class ComprehensivePhpDocExample
{
    /**
     * @var int|null The ID of the entity
     */
    private ?int $id = null;

    /**
     * @var string The name of the entity
     */
    private string $name;

    /**
     * @var array<int, string> List of tags
     */
    private array $tags = [];

    /**
     * Constructor
     *
     * @param string $name The name
     * @param int|null $id The ID
     */
    public function __construct(string $name, ?int $id = null)
    {
        $this->name = $name;
        $this->id = $id;
    }

    /**
     * Get the ID
     *
     * @return int|null The ID or null if not set
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the ID
     *
     * @param int|null $id The new ID
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the name
     *
     * @return string The name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name
     *
     * @param string $name The new name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get tags
     *
     * @return array<int, string> List of tags
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Set tags
     *
     * @param array<int, string> $tags New tags
     * @return self
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * Add a tag
     *
     * @param string $tag The tag to add
     * @return self
     * @throws \InvalidArgumentException If tag is empty
     */
    public function addTag(string $tag): self
    {
        if (empty($tag)) {
            throw new \InvalidArgumentException('Tag cannot be empty');
        }
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * Process the entity
     *
     * @param callable(string): bool $validator
     * @param array<string, mixed> $options
     * @return array{success: bool, data: mixed}
     * @throws \RuntimeException If processing fails
     */
    public function process(callable $validator, array $options = []): array
    {
        if (!$validator($this->name)) {
            throw new \RuntimeException('Validation failed');
        }
        return ['success' => true, 'data' => $this->toArray()];
    }

    /**
     * Convert to array
     *
     * @return array{
     *     id: int|null,
     *     name: string,
     *     tags: array<int, string>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tags' => $this->tags,
        ];
    }

    /**
     * Create a clone
     *
     * @return static
     */
    public function clone(): static
    {
        return clone $this;
    }

    /**
     * Magical toString
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}

// -----------------------------------------------------------------------------
// CASE 042
// File-level final class with multiple nested structures
// -----------------------------------------------------------------------------

final class FileLevelTestClass
{
    private static ?self $instance = null;

    private function __construct()
    {
        // private constructor
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function testAnonymousClass(): object
    {
        return new class extends AbstractEntity {
            private string $value = 'test';

            public function save(): bool
            {
                return true;
            }

            public function toArray(): array
            {
                return ['value' => $this->value];
            }

            public function getValue(): string
            {
                return $this->value;
            }
        };
    }

    public function testClosure(): Closure
    {
        return function (int $x): int {
            return $x * 2;
        };
    }

    public function testArrowFunction(): Closure
    {
        return fn(int $x): int => $x * 3;
    }

    public function testGenerator(): Generator
    {
        for ($i = 0; $i < 10; $i++) {
            yield $i => $i * 2;
        }
    }
}

// -----------------------------------------------------------------------------
// CASE 043
// Final statement - ensure file ends with no extra whitespace
// -----------------------------------------------------------------------------

// End of file