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

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\TestCase;

class DependencyFailureTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(false);
    }

    #[Depends('testOne')]
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('testTwo')]
    public function testThree(): void
    {
        $this->assertTrue(true);
    }

    #[DependsUsingShallowClone('testOne')]
    public function testFour(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('doesNotExist')]
    public function testHandlesDependencyOnTestMethodThatDoesNotExist(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('')]
    public function testHandlesDependencyOnTestMethodWithEmptyName(): void
    {
        $this->assertTrue(true);
    }
}
