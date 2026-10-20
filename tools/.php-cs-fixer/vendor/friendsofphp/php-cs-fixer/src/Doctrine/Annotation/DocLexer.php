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

namespace PhpCsFixer\Doctrine\Annotation;

use PhpCsFixer\Preg;

/**
 * Copyright (c) 2006-2013 Doctrine Project.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DocLexer
{
    public const T_NONE = 1;
    public const T_INTEGER = 2;
    public const T_STRING = 3;
    public const T_FLOAT = 4;

    // All tokens that are also identifiers should be >= 100
    public const T_IDENTIFIER = 100;
    public const T_AT = 101;
    public const T_CLOSE_CURLY_BRACES = 102;
    public const T_CLOSE_PARENTHESIS = 103;
    public const T_COMMA = 104;
    public const T_EQUALS = 105;
    public const T_NAMESPACE_SEPARATOR = 107;
    public const T_OPEN_CURLY_BRACES = 108;
    public const T_OPEN_PARENTHESIS = 109;
    public const T_COLON = 112;
    public const T_MINUS = 113;

    private const CATCHABLE_PATTERNS = [
        '[a-z_\\\][a-z0-9_\:\\\]*[a-z_][a-z0-9_]*',
        '(?:[+-]?[0-9]+(?:[\.][0-9]+)*)(?:[eE][+-]?[0-9]+)?',
        '"(?:""|[^"])*+"',
    ];

    private const NON_CATCHABLE_PATTERNS = ['\s+', '\*+', '(.)'];

    /** @var array<string, self::T_*> */
    private array $noCase = [
        '@' => self::T_AT,
        ',' => self::T_COMMA,
        '(' => self::T_OPEN_PARENTHESIS,
        ')' => self::T_CLOSE_PARENTHESIS,
        '{' => self::T_OPEN_CURLY_BRACES,
        '}' => self::T_CLOSE_CURLY_BRACES,
        '=' => self::T_EQUALS,
        ':' => self::T_COLON,
        '-' => self::T_MINUS,
        '\\' => self::T_NAMESPACE_SEPARATOR,
    ];

    /** @var list<Token> */
    private array $tokens = [];

    private int $position = 0;

    private int $peek = 0;

    private ?string $regex = null;

    public function setInput(string $input): void
    {
        $this->tokens = [];
        $this->reset();
        $this->scan($input);
    }

    public function reset(): void
    {
        $this->peek = 0;
        $this->position = 0;
    }

    public function peek(): ?Token
    {
        return $this->tokens[$this->position + $this->peek++] ?? null;
    }

    /**
     * @return self::T_*
     */
    private function getType(string &$value): int
    {
        $type = self::T_NONE;

        if ('"' === $value[0]) {
            $value = str_replace('""', '"', substr($value, 1, \strlen($value) - 2));

            return self::T_STRING;
        }

        if (isset($this->noCase[$value])) {
            return $this->noCase[$value];
        }

        if ('_' === $value[0] || '\\' === $value[0] || !Preg::match('/[^A-Za-z]/', $value[0])) {
            return self::T_IDENTIFIER;
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') || str_contains(strtolower($value), 'e')
                ? self::T_FLOAT : self::T_INTEGER;
        }

        return $type;
    }

    private function scan(string $input): void
    {
        $this->regex ??= \sprintf(
            '/(%s)|%s/%s',
            implode(')|(', self::CATCHABLE_PATTERNS),
            implode('|', self::NON_CATCHABLE_PATTERNS),
            'iu',
        );

        $flags = \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_OFFSET_CAPTURE;
        $matches = Preg::split($this->regex, $input, -1, $flags);

        foreach ($matches as $match) {
            // Must remain before 'value' assignment since it can change content
            $firstMatch = $match[0];
            $type = $this->getType($firstMatch);

            $this->tokens[] = new Token($type, $firstMatch, (int) $match[1]);
        }
    }
}
