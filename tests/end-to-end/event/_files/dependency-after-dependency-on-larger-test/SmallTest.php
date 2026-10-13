<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
final class SmallTest extends TestCase
{
    public function testOne(): string
    {
        $this->assertTrue(true);

        return 'small';
    }

    public function testTwo(): string
    {
        $this->fail();
    }

    #[DependsExternal(LargeTest::class, 'testOne')]
    #[Depends('testOne')]
    public function testThree(string $large, string $small): void
    {
        $this->assertSame('large', $large);
        $this->assertSame('small', $small);
    }

    #[DependsExternal(LargeTest::class, 'testOne')]
    #[Depends('testTwo')]
    public function testFour(string $large, string $small): void
    {
        $this->assertSame('large', $large);
        $this->assertSame('small', $small);
    }
}
