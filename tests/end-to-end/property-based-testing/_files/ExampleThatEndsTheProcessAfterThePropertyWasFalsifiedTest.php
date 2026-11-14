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

use function sprintf;
use PHPUnit\Framework\Attributes\CounterexampleFormatter;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class ExampleThatEndsTheProcessAfterThePropertyWasFalsifiedTest extends TestCase
{
    private static bool $falsified = false;
    private static bool $shrinking = false;

    /**
     * @return ValueGenerator<int>
     */
    public static function integers(): ValueGenerator
    {
        return Generators::integers(0, 1000);
    }

    public static function format(int $value): string
    {
        return sprintf('formatted %d', $value);
    }

    #[Property]
    public function testExitsWhenTheExampleThatFalsifiedThePropertyIsReplayed(#[ForAll('integers')] int $value): void
    {
        if ($value >= 100 && self::$falsified) {
            exit(1);
        }

        if ($value >= 100) {
            self::$falsified = true;
        }

        $this->assertLessThan(100, $value);
    }

    #[Property]
    #[CounterexampleFormatter('format')]
    public function testExitsWhileTheCounterexampleIsShrunk(#[ForAll('integers')] int $value): void
    {
        $this->draw(Generators::elements('drawn'), 'label');

        if ($value >= 50 && $value < 100 && self::$shrinking) {
            exit(1);
        }

        if ($value >= 100) {
            self::$shrinking = true;
        }

        $this->assertLessThan(100, $value);
    }
}
