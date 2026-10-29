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
final class CodeSampleList
{
    /**
     * @var list<CodeSample>
     */
    private array $codeSamples;

    /**
     * @param list<CodeSample> $codeSamples
     */
    private function __construct(array $codeSamples)
    {
        $this->codeSamples = $codeSamples;
    }

    public static function create(CodeSample ...$codeSamples): self
    {
        return new self(\array_values($codeSamples));
    }

    /**
     * @return list<CodeSample>
     */
    public function toArray(): array
    {
        return $this->codeSamples;
    }
}
