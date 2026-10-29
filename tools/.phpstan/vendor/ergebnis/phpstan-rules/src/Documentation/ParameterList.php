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
final class ParameterList
{
    /**
     * @var list<Parameter>
     */
    private array $parameters;

    /**
     * @param list<Parameter> $parameters
     */
    private function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public static function create(Parameter ...$parameters): self
    {
        return new self(\array_values($parameters));
    }

    /**
     * @return list<Parameter>
     */
    public function toArray(): array
    {
        return $this->parameters;
    }
}
