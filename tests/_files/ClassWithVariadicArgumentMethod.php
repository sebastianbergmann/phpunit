<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A class with a method that takes a variadic argument.
 */
class ClassWithVariadicArgumentMethod
{
    public function foo(...$args)
    {
        return $args;
    }
}
