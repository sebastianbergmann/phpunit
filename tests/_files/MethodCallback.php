<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function func_get_args;

class MethodCallback
{
    public static function staticCallback()
    {
        $args = func_get_args();

        if ($args == ['foo', 'bar']) {
            return 'pass';
        }
    }

    public function nonStaticCallback()
    {
        $args = func_get_args();

        if ($args == ['foo', 'bar']) {
            return 'pass';
        }
    }
}
