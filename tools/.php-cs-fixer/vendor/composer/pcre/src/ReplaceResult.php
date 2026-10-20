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

final class ReplaceResult
{
    /**
     * @readonly
     * @var string
     */
    public $result;

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
     */
    public function __construct(int $count, string $result)
    {
        $this->count = $count;
        $this->matched = (bool) $count;
        $this->result = $result;
    }
}
