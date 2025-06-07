<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6222;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class Issue6222Test extends TestCase
{
    public static function provider(): iterable
    {
        yield [1];

        yield [2];
    }

    public function testOne(): void
    {
        $this->assertTrue(false);
    }

    #[DataProvider('provider')]
    public function testTwoCasesPassing(int $x): void
    {
        $this->assertGreaterThan(0, $x);
    }

    #[Depends('testTwoCasesPassing')]
    public function testDependingOnTwoCasesPassing(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('provider')]
    public function testOneCasePassing(int $x): void
    {
        $this->assertSame(1, $x);
    }

    #[Depends('testOneCasePassing')]
    public function testDependingOnOneCasePassing(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('provider')]
    public function testZeroCasesPassing(int $x): void
    {
        $this->assertSame(3, $x);
    }

    #[Depends('testZeroCasesPassing')]
    public function testDependingOnZeroCasesPassing(): void
    {
        $this->assertTrue(true);
    }
}
