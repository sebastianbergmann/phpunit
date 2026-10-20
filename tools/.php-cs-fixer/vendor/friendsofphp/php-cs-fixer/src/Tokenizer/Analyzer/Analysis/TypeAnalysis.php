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

namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TypeAnalysis
{
    /**
     * This list contains soft and hard reserved types that can be used or will be used by PHP at some point.
     *
     * More info:
     *
     * @var non-empty-list<string>
     *
     * @see https://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration.types
     * @see https://php.net/manual/en/reserved.other-reserved-words.php
     */
    private const RESERVED_TYPES = [
        'array',
        'bool',
        'callable',
        'false',
        'float',
        'int',
        'iterable',
        'list',
        'mixed',
        'never',
        'null',
        'object',
        'parent',
        'resource',
        'self',
        'static',
        'string',
        'true',
        'void',
    ];

    private string $name;

    private ?int $startIndex;

    private ?int $endIndex;

    private bool $nullable;

    /**
     * @param ($startIndex is null ? null : int) $endIndex
     */
    public function __construct(string $name, ?int $startIndex = null, ?int $endIndex = null)
    {
        if (str_starts_with($name, '?')) {
            $this->name = substr($name, 1);
            $this->nullable = true;
        } elseif (\PHP_VERSION_ID >= 8_00_00) {
            $this->name = $name;
            $this->nullable = \in_array('null', array_map('trim', explode('|', strtolower($name))), true);
        } else {
            $this->name = $name;
            $this->nullable = false;
        }

        $this->startIndex = $startIndex;
        $this->endIndex = $endIndex;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStartIndex(): int
    {
        if (null === $this->startIndex) {
            throw new \RuntimeException('TypeAnalysis: no start index.');
        }

        return $this->startIndex;
    }

    public function getEndIndex(): int
    {
        if (null === $this->endIndex) {
            throw new \RuntimeException('TypeAnalysis: no end index.');
        }

        return $this->endIndex;
    }

    public function isReservedType(): bool
    {
        return \in_array(strtolower($this->name), self::RESERVED_TYPES, true);
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
