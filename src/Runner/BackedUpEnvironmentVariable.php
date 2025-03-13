<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use function getenv;
use function putenv;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class BackedUpEnvironmentVariable
{
    private const string FROM_GETENV      = 'getenv';
    private const string FROM_SUPERGLOBAL = 'superglobal';

    /**
     * @var self::FROM_GETENV|self::FROM_SUPERGLOBAL
     */
    private string $from;

    /**
     * @var non-empty-string
     */
    private string $name;
    private null|string $value;

    /**
     * @param non-empty-string $name
     *
     * @return array{0: self, 1: self}
     */
    public static function create(string $name): array
    {
        $getenv = getenv($name);

        if ($getenv === false) {
            $getenv = null;
        }

        return [
            new self(self::FROM_SUPERGLOBAL, $name, $_ENV[$name] ?? null),
            new self(self::FROM_GETENV, $name, $getenv),
        ];
    }

    /**
     * @param self::FROM_GETENV|self::FROM_SUPERGLOBAL $from
     * @param non-empty-string                         $name
     */
    private function __construct(string $from, string $name, null|string $value)
    {
        $this->from  = $from;
        $this->name  = $name;
        $this->value = $value;
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
