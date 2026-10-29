<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Documentation;

/**
 * @internal
 */
final class ParameterExample
{
    /**
     * @var bool|list<array<string, string>>|list<string>
     */
    private $value;

    /**
     * @param bool|list<array<string, string>>|list<string> $value
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    public static function fromStrings(string ...$values): self
    {
        return new self(\array_values($values));
    }

    /**
     * @param array<string, string> ...$maps
     */
    public static function fromMaps(array ...$maps): self
    {
        return new self(\array_values($maps));
    }

    /**
     * @return bool|list<array<string, string>>|list<string>
     */
    public function toValue()
    {
        return $this->value;
    }
}
