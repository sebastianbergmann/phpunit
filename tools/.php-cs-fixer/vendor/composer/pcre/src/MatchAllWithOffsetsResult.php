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

final class MatchAllWithOffsetsResult
{
    /**
     * An array of match group => list of matches, every match being a pair of string matched + offset in bytes (or -1 if no match)
     *
     * @readonly
     * @var array<int|string, list<array{string|null, int}>>
     * @phpstan-var array<int|string, list<array{string|null, int<-1, max>}>>
     */
    public $matches;

    /**
     * @readonly
     * @var 0|positive-int
     */
    public $count;

    /**
     * @readonly
     * @var bool
     */
    public $matched;

    /**
     * @param 0|positive-int $count
     * @param array<int|string, list<array{string|null, int}>> $matches
     * @phpstan-param array<int|string, list<array{string|null, int<-1, max>}>> $matches
     */
    public function __construct(int $count, array $matches)
    {
        $this->matches = $matches;
        $this->matched = (bool) $count;
        $this->count = $count;
    }
}
