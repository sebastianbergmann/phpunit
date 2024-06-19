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

class ExtendableClassCallingMethodInConstructor
{
    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
    }

    public function second(): void
    {
        $this->reset();
    }
}
