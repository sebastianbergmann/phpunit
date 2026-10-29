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
    private string $name;
    private string $description;

    /**
     * @var bool|list<array<string, string>>|list<string>
     */
    private $example;

    /**
     * @param bool|list<array<string, string>>|list<string> $example
     */
    private function __construct(
        string $name,
        string $description,
        $example
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->example = $example;
    }

    /**
     * @param bool|list<array<string, string>>|list<string> $example
     */
    public static function create(
        string $name,
        string $description,
        $example
    ): self {
        return new self(
            $name,
            $description,
            $example,
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns a description of the parameter, in Markdown.
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * Returns an example value for the parameter.
     *
     * @return bool|list<array<string, string>>|list<string>
     */
    public function example()
    {
        return $this->example;
    }
}
