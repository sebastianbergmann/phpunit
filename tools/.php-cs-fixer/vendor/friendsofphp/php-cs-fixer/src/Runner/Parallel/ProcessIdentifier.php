<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Runner\Parallel;

/**
 * Represents identifier of single process that is handled within parallel run.
 *
 * @author Greg Korba <greg@codito.dev>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ProcessIdentifier
{
    private const IDENTIFIER_PREFIX = 'php-cs-fixer_parallel_';

    private string $identifier;

    private function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function toString(): string
    {
        return $this->identifier;
    }

    public static function create(): self
    {
        return new self(uniqid(self::IDENTIFIER_PREFIX, true));
    }

    public static function fromRaw(string $identifier): self
    {
        if (!str_starts_with($identifier, self::IDENTIFIER_PREFIX)) {
            throw new ParallelisationException(\sprintf('Invalid process identifier "%s".', $identifier));
        }

        return new self($identifier);
    }
}
