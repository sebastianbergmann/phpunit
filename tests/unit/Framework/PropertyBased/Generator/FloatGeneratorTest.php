<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use const INF;
use const NAN;
use const PHP_FLOAT_EPSILON;
use const PHP_FLOAT_MAX;
use const PHP_FLOAT_MIN;
use function serialize;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(FloatGenerator::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class FloatGeneratorTest extends TestCase
{
    /**
     * @return array<string, array{float}>
     */
    public static function floatProvider(): array
    {
        return [
            'zero'                    => [0.0],
            'negative zero'           => [-0.0],
            'one'                     => [1.0],
            'negative integer'        => [-42.0],
            'fraction'                => [0.5],
            'fraction above one'      => [1.5],
            'decimal fraction'        => [0.1],
            'largest integral'        => [9007199254740992.0],
            'beyond largest integral' => [9007199254740994.0],
            '2 ** 63'                 => [9.2233720368547758E+18],
            'epsilon'                 => [PHP_FLOAT_EPSILON],
            'smallest normal'         => [PHP_FLOAT_MIN],
            'smallest subnormal'      => [4.9E-324],
            'largest finite'          => [PHP_FLOAT_MAX],
            'negative largest finite' => [-PHP_FLOAT_MAX],
            'infinity'                => [INF],
            'negative infinity'       => [-INF],
            'NaN'                     => [NAN],
        ];
    }

    #[DataProvider('floatProvider')]
    public function testDecodingTheEncodingOfAFloatYieldsTheFloat(float $value): void
    {
        [$sign, $kind, $magnitude] = FloatGenerator::encode($value);

        $this->assertSame(serialize($value), serialize(FloatGenerator::decode($sign, $kind, $magnitude)));
    }

    public function testSmallIntegersAreEncodedAsTheirMagnitude(): void
    {
        $this->assertSame([0, 0, 0], FloatGenerator::encode(0.0));
        $this->assertSame([1, 0, 0], FloatGenerator::encode(-0.0));
        $this->assertSame([0, 0, 3], FloatGenerator::encode(3.0));
        $this->assertSame([1, 0, 3], FloatGenerator::encode(-3.0));
    }

    public function testFractionsWithFewerBitsAndExponentsCloserToZeroAreSimpler(): void
    {
        $this->assertSame(1, FloatGenerator::encode(0.5)[1]);

        $this->assertLessThan(FloatGenerator::encode(1.25)[2], FloatGenerator::encode(1.5)[2]);
        $this->assertLessThan(FloatGenerator::encode(0.5)[2], FloatGenerator::encode(1.5)[2]);
        $this->assertLessThan(FloatGenerator::encode(0.25)[2], FloatGenerator::encode(0.5)[2]);
        $this->assertLessThan(FloatGenerator::encode(0.1)[2], FloatGenerator::encode(0.5)[2]);
    }
}
