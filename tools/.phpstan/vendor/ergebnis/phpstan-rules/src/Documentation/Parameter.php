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
final class Parameter
{
    private ParameterName $name;
    private Description $description;
    private ParameterExample $example;

    private function __construct(
        ParameterName $name,
        Description $description,
        ParameterExample $example
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->example = $example;
    }

    public static function create(
        ParameterName $name,
        Description $description,
        ParameterExample $example
    ): self {
        return new self(
            $name,
            $description,
            $example,
        );
    }

    public function name(): ParameterName
    {
        return $this->name;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function example(): ParameterExample
    {
        return $this->example;
    }
}
