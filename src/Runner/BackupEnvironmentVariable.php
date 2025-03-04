<?php

declare(strict_types=1);

namespace PHPUnit\Runner;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @codeCoverageIgnore
 */
final readonly class BackupEnvironmentVariable
{
    private const FROM_GETENV = 'getenv';
    private const FROM_SUPERGLOBAL = 'superglobal';

    /**
     * @var self::FROM_*
     */
    private string $from;

    /**
     * @var non-empty-string
     */
    private string $name;
    private string|null $value;

    /**
     * @param self::FROM_* $from
     * @param non-empty-string $name
     */
    private function __construct(string $from, string $name, string|null $value)
    {
        $this->from  = $from;
        $this->name  = $name;
        $this->value = $value;
    }

    /**
     * @param non-empty-string $name
     * @return array{0: self, 1: self}
     */
    public static function create(string $name): array
    {
        return [
            new self(self::FROM_SUPERGLOBAL, $name, $_ENV[$name] ?? null),
            new self(self::FROM_GETENV, $name, getenv($name) ?: null),
        ];
    }

    public function restore(): void
    {
        if ($this->from === self::FROM_GETENV) {
            $this->restoreGetEnv();
        } else {
            $this->restoreSuperGlobal();
        }
    }

    private function restoreGetEnv(): void
    {
        if ($this->value === null) {
            putenv($this->name);
        } else {
            putenv("{$this->name}={$this->value}");
        }
    }

    private function restoreSuperGlobal(): void
    {
        if ($this->value === null) {
            unset($_ENV[$this->name]);
        } else {
            $_ENV[$this->name] = $this->value;
        }
    }
}
