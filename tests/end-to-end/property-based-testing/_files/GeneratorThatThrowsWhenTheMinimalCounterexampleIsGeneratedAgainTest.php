<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class GeneratorThatThrowsWhenTheMinimalCounterexampleIsGeneratedAgainTest extends TestCase
{
    private static int $numberOfValues = 0;

    /**
     * The value is generated for the example that falsifies the property and
     * for its four replays; the value draws no choices, so there is nothing
     * to shrink, and the sixth value is the one for the final replay of the
     * minimal counterexample.
     *
     * @return ValueGenerator<int>
     */
    public static function values(): ValueGenerator
    {
        return Generators::constant(0)->map(
            static function (int $value): int
            {
                self::$numberOfValues++;

                if (self::$numberOfValues > 5) {
                    throw new RuntimeException('The minimal counterexample cannot be generated again');
                }

                return $value;
            },
        );
    }

    #[Property]
    public function testIsFalsifiedByTheFirstExample(#[ForAll('values')] int $value): void
    {
        $this->assertSame(1, $value);
    }
}
