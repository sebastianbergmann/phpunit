<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactAnalysis;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesFixture;
use PHPUnit\Framework\TestCase;

#[CoversClass(Money::class)]
#[UsesFixture('fixtures/scenarios')]
final class TestThatUsesFixtures extends TestCase
{
    #[UsesFixture('fixtures/sums.csv')]
    public static function provideSums(): array
    {
        return [[1, 2, 3]];
    }

    #[UsesFixture('fixtures/one.txt')]
    public function testDeclaredOnTheMethod(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('provideSums')]
    public function testDeclaredOnTheDataProvider(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, new Money($a)->add(new Money($b))->amount());
    }

    #[UsesFixture('fixtures/does-not-exist.csv')]
    public function testDeclaredButNotThere(): void
    {
        $this->assertTrue(true);
    }
}
