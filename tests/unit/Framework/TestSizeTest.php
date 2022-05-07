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
#[CoversClass(Small::class)]
#[CoversClass(TestSize::class)]
#[CoversClass(Unknown::class)]
#[Small]
final class TestSizeTest extends TestCase
{
    public function testCanBeUnknown(): void
    {
        $status = TestSize::unknown();

        $this->assertFalse($status->isKnown());
        $this->assertTrue($status->isUnknown());
        $this->assertFalse($status->isSmall());
        $this->assertFalse($status->isMedium());
        $this->assertFalse($status->isLarge());

        $this->assertSame('unknown', $status->asString());
    }

    public function testCanBeSmall(): void
    {
        $status = TestSize::small();

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertTrue($status->isSmall());
        $this->assertFalse($status->isMedium());
        $this->assertFalse($status->isLarge());

        $this->assertSame('small', $status->asString());
    }

    public function testCanBeMedium(): void
    {
        $status = TestSize::medium();

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSmall());
        $this->assertTrue($status->isMedium());
        $this->assertFalse($status->isLarge());

        $this->assertSame('medium', $status->asString());
    }

    public function testCanBeLarge(): void
    {
        $status = TestSize::large();

        $this->assertTrue($status->isKnown());
        $this->assertFalse($status->isUnknown());
        $this->assertFalse($status->isSmall());
        $this->assertFalse($status->isMedium());
        $this->assertTrue($status->isLarge());

        $this->assertSame('large', $status->asString());
    }

    #[DataProvider('comparisonProvider')]
    public function testTwoKnownSizesCanBeCompared(bool $expected, Known $a, Known $b): void
    {
        $this->assertSame($expected, $a->isGreaterThan($b));
    }

    public function comparisonProvider(): array
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
}
