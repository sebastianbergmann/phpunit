<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fidry\CpuCoreCounter;

use function function_exists;
use function getenv;

/**
 * @private
 */
final class Env
{
    /**
     * @return string|false
     */
    public static function get(string $name)
    {
        // The function may be disabled, e.g. with disable_functions.
        return function_exists('getenv') ? getenv($name) : false;
    }

    private function __construct()
    {
    }
}
