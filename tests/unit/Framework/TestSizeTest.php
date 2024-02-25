<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestSize;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Known::class)]
#[CoversClass(Large::class)]
#[CoversClass(Medium::class)]
#[CoversClass(\PHPUnit\Framework\TestSize\Small::class)]
#[CoversClass(TestSize::class)]
#[CoversClass(Unknown::class)]
#[Small]
final class TestSizeTest extends TestCase
{
    public static function comparisonProvider(): array
    {
        return [
            'small test is not greater than small test' => [
                false,
                TestSize::small(),
                TestSize::small(),
            ],

            'small test is not greater than medium test' => [
                false,
                TestSize::small(),
                TestSize::medium(),
            ],

            'small test is not greater than large test' => [
                false,
                TestSize::small(),
                TestSize::large(),
            ],

            'medium test is greater than small test' => [
                true,
                TestSize::medium(),
                TestSize::small(),
            ],

            'medium test is not greater than medium test' => [
                false,
                TestSize::medium(),
                TestSize::medium(),
            ],

            'medium test is not greater than large test' => [
                false,
                TestSize::medium(),
                TestSize::large(),
            ],

            'large test is greater than small test' => [
                true,
                TestSize::large(),
                TestSize::small(),
            ],

            'large test is greater than medium test' => [
                true,
                TestSize::large(),
                TestSize::medium(),
            ],

            'large test is not greater than large test' => [
                false,
                TestSize::large(),
                TestSize::large(),
            ],
        ];
    }

    public function testCanBeUnknown(): void
    {
        $testSize = TestSize::unknown();

        $this->assertFalse($testSize->isKnown());
        $this->assertTrue($testSize->isUnknown());
        $this->assertFalse($testSize->isSmall());
        $this->assertFalse($testSize->isMedium());
        $this->assertFalse($testSize->isLarge());

        $this->assertSame('unknown', $testSize->asString());
    }

    public function testCanBeSmall(): void
    {
        $testSize = TestSize::small();

        $this->assertTrue($testSize->isKnown());
        $this->assertFalse($testSize->isUnknown());
        $this->assertTrue($testSize->isSmall());
        $this->assertFalse($testSize->isMedium());
        $this->assertFalse($testSize->isLarge());

        $this->assertSame('small', $testSize->asString());
    }

    public function testCanBeMedium(): void
    {
        $testSize = TestSize::medium();

        $this->assertTrue($testSize->isKnown());
        $this->assertFalse($testSize->isUnknown());
        $this->assertFalse($testSize->isSmall());
        $this->assertTrue($testSize->isMedium());
        $this->assertFalse($testSize->isLarge());

        $this->assertSame('medium', $testSize->asString());
    }

    public function testCanBeLarge(): void
    {
        $testSize = TestSize::large();

        $this->assertTrue($testSize->isKnown());
        $this->assertFalse($testSize->isUnknown());
        $this->assertFalse($testSize->isSmall());
        $this->assertFalse($testSize->isMedium());
        $this->assertTrue($testSize->isLarge());

        $this->assertSame('large', $testSize->asString());
    }

    #[DataProvider('comparisonProvider')]
    public function testTwoKnownSizesCanBeCompared(bool $expected, Known $a, Known $b): void
    {
        $this->assertSame($expected, $a->isGreaterThan($b));
    }
}
