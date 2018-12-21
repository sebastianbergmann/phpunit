<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class FunctionCallbackWrapper
{
    public static function functionCallback()
    {
        $args = \func_get_args();

        if ($args == ['foo', 'bar']) {
            return 'pass';
        }
    }
}
