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

class Mockable
{
    public $constructorArgs;

    public $cloned;

    public function __construct($arg1 = null, $arg2 = null)
    {
        $this->constructorArgs = [$arg1, $arg2];
    }

    public function __clone()
    {
        $this->cloned = true;
    }

    public function mockableMethod()
    {
        // something different from NULL
        return true;
    }

    public function anotherMockableMethod()
    {
        // something different from NULL
        return true;
    }
}
