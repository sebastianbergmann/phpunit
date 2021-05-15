<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\DependsExternalUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsExternalUsingShallowClone;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsOnClassUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsOnClassUsingShallowClone;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\TestCase;

final class DependencyTest extends TestCase
{
    #[Depends('testOne')]
    public function testOne(): void
    {
    }

    #[DependsUsingDeepClone('testOne')]
    public function testTwo(): void
    {
    }

    #[DependsUsingShallowClone('testOne')]
    public function testThree(): void
    {
    }

    #[DependsExternal(AnotherTest::class, 'testOne')]
    public function testFour(): void
    {
    }

    #[DependsExternalUsingDeepClone(AnotherTest::class, 'testOne')]
    public function testFive(): void
    {
    }

    #[DependsExternalUsingShallowClone(AnotherTest::class, 'testOne')]
    public function testSix(): void
    {
    }

    #[DependsOnClass(AnotherTest::class)]
    public function testSeven(): void
    {
    }

    #[DependsOnClassUsingDeepClone(AnotherTest::class)]
    public function testEight(): void
    {
    }

    #[DependsOnClassUsingShallowClone(AnotherTest::class)]
    public function testNine(): void
    {
    }
}
