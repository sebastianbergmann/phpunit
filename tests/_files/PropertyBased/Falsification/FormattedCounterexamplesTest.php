<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Falsification;

use const E_USER_WARNING;
use function ob_start;
use function sprintf;
use function trigger_error;
use PHPUnit\Framework\Attributes\CounterexampleFormatter;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FormattedCounterexamplesTest extends TestCase
{
    /**
     * @var non-negative-int
     */
    public static int $numberOfCalls = 0;

    /**
     * @var non-negative-int
     */
    public static int $numberOfRuns = 0;

    /**
     * @return ValueGenerator<int>
     */
    public static function digits(): ValueGenerator
    {
        return Generators::integers(0, 9);
    }

    public static function countsCalls(int $value): string
    {
        self::$numberOfCalls++;

        return sprintf('value %d, call %d', $value, self::$numberOfCalls);
    }

    public static function raises(int $value): string
    {
        throw new RuntimeException('message');
    }

    public static function returnsAnInteger(int $value): int
    {
        return $value;
    }

    public static function returnsAnEmptyString(int $value): string
    {
        return '';
    }

    public static function printsOutput(int $value): string
    {
        print 'output';

        return sprintf('value %d', $value);
    }

    public static function printsOutputIntoBufferThatItDoesNotEnd(int $value): string
    {
        ob_start();

        print 'output';

        return sprintf('value %d', $value);
    }

    public static function triggersWarning(int $value): string
    {
        trigger_error('message', E_USER_WARNING);

        return sprintf('value %d', $value);
    }

    #[Property]
    public function testWithoutFormatter(#[ForAll('digits')] int $value): void
    {
        $this->assertLessThan(5, $value);
    }

    #[Property]
    #[CounterexampleFormatter('countsCalls')]
    public function testIsFalsifiedFromFive(#[ForAll('digits')] int $value): void
    {
        $this->assertLessThan(5, $value);
    }

    #[Property]
    #[CounterexampleFormatter('countsCalls')]
    public function testIsFalsifiedOnlyTheFirstTime(#[ForAll('digits')] int $value): void
    {
        self::$numberOfRuns++;

        $this->assertGreaterThan(1, self::$numberOfRuns);
    }

    #[Property]
    #[CounterexampleFormatter('countsCalls')]
    public function testIsFalsifiedInTwoWays(#[ForAll('digits')] int $value): void
    {
        if ($value >= 3 && $value < 5) {
            throw new RuntimeException('error');
        }

        $this->assertLessThan(5, $value);
    }

    #[Property]
    #[CounterexampleFormatter('raises')]
    public function testWithFormatterThatRaisesAnException(#[ForAll('digits')] int $value): void
    {
        $this->assertLessThan(5, $value);
    }

    #[Property]
    #[CounterexampleFormatter('returnsAnInteger')]
    public function testWithFormatterThatDoesNotReturnAString(#[ForAll('digits')] int $value): void
    {
        $this->assertLessThan(5, $value);
    }

    #[Property]
    #[CounterexampleFormatter('returnsAnEmptyString')]
    public function testWithFormatterThatReturnsAnEmptyString(#[ForAll('digits')] int $value): void
    {
        $this->assertLessThan(5, $value);
    }

    #[Property]
    #[CounterexampleFormatter('printsOutput')]
    public function testWithFormatterThatPrintsOutput(#[ForAll('digits')] int $value): void
    {
        $this->assertLessThan(5, $value);
    }

    #[Property]
    #[CounterexampleFormatter('printsOutputIntoBufferThatItDoesNotEnd')]
    public function testWithFormatterThatPrintsOutputIntoBufferThatItDoesNotEnd(#[ForAll('digits')] int $value): void
    {
        $this->assertLessThan(5, $value);
    }

    #[Property]
    #[CounterexampleFormatter('triggersWarning')]
    public function testWithFormatterThatTriggersWarning(#[ForAll('digits')] int $value): void
    {
        $this->assertLessThan(5, $value);
    }
}
