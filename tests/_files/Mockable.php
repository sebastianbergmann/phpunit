<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Mockable
{
    public $constructorCalled = false;
    public $cloned            = false;

    public function __construct()
    {
        $this->constructorCalled = false;
    }

    public function __clone()
    {
        $this->cloned = true;
    }

    public function foo()
    {
        return true;
    }

    public function bar()
    {
        return true;
    }
}
