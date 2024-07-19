<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\MockObject;

class ExtendableClass
{
    public bool $constructorCalled = false;

    public function __construct()
    {
        $this->constructorCalled = true;
    }

    public function __destruct()
    {
    }

    public function doSomething(): bool
    {
        return $this->doSomethingElse();
    }

    public function doSomethingElse(): bool
    {
        return false;
    }

    final public function finalMethod(): void
    {
    }

    private function privateMethod(): void
    {
    }
}
