<?php

/*
 * This file is part of composer/pcre.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\Pcre;

final class MatchWithOffsetsResult
{
    /**
     * An array of match group => pair of string matched + offset in bytes (or -1 if no match)
     *
     * @readonly
     * @var array<int|string, array{string|null, int}>
     * @phpstan-var array<int|string, array{string|null, int<-1, max>}>
     */
    public $matches;

    /**
     * @readonly
     * @var bool
     */
    public $matched;

    /**
     * @param 0|positive-int $count
     * @param array<array{string|null, int}> $matches
     * @phpstan-param array<int|string, array{string|null, int<-1, max>}> $matches
     */
    public function __construct(int $count, array $matches)
    {
        $this->matches = $matches;
        $this->matched = (bool) $count;
    }
}
