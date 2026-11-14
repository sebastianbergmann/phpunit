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

use const E_USER_WARNING;
use function count;
use function sprintf;
use function trigger_error;
use ArrayObject;
use PHPUnit\Framework\Attributes\CounterexampleFormatter;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CounterexampleFormatterCallsTest extends TestCase
{
    private static int $numberOfCalls                     = 0;
    private static int $numberOfExamplesOfPropertyIsFlaky = 0;

    /**
     * @return ValueGenerator<int>
     */
    public static function integers(): ValueGenerator
    {
        return Generators::integers(0, 1000);
    }

    /**
     * @return ValueGenerator<ArrayObject<int, int>>
     */
    public static function stacks(): ValueGenerator
    {
        return Generators::lists(Generators::integers(0, 9), 0, 10)->map(
            static fn (array $values): ArrayObject => new ArrayObject($values),
        );
    }

    public static function countsCalls(int $value): string
    {
        self::$numberOfCalls++;

        return sprintf('value %d, call %d of the formatter', $value, self::$numberOfCalls);
    }

    /**
     * @param ArrayObject<int, int> $stack
     */
    public static function emptiesStack(ArrayObject $stack): string
    {
        $size = count($stack);

        $stack->exchangeArray([]);

        return sprintf('stack of %d', $size);
    }

    public static function triggersWarning(int $value): string
    {
        trigger_error('triggered by formatter', E_USER_WARNING);

        return sprintf('value %d', $value);
    }

    public static function printsOutput(int $value): string
    {
        print 'printed by formatter';

        return sprintf('value %d', $value);
    }

    #[Property]
    #[CounterexampleFormatter('countsCalls')]
    public function testFormatterIsCalledOnceForMinimalCounterexample(#[ForAll('integers')] int $value): void
    {
        $this->assertLessThan(100, $value);
    }

    #[Property]
    #[CounterexampleFormatter('countsCalls')]
    public function testFormatterIsCalledOnceForCounterexampleOfFlakyProperty(#[ForAll('integers')] int $value): void
    {
        self::$numberOfExamplesOfPropertyIsFlaky++;

        $this->assertNotSame(3, self::$numberOfExamplesOfPropertyIsFlaky);
    }

    #[Property]
    #[TestWith([50])]
    #[TestWith([500])]
    #[CounterexampleFormatter('countsCalls')]
    public function testFormatterIsCalledOnceForExplicitExampleThatFalsifiesProperty(#[ForAll('integers')] int $value): void
    {
        $this->assertLessThan(100, $value);
    }

    #[Property]
    #[CounterexampleFormatter('countsCalls')]
    public function testFormatterIsCalledOnceForEachWayInWhichPropertyIsFalsified(#[ForAll('integers')] int $value): void
    {
        if ($value >= 10 && $value < 50) {
            throw new RuntimeException('message');
        }

        $this->assertLessThan(50, $value);
    }

    /**
     * @param ArrayObject<int, int> $stack
     */
    #[Property]
    #[CounterexampleFormatter('emptiesStack')]
    public function testFormatterThatChangesItsArgument(#[ForAll('stacks')] ArrayObject $stack): void
    {
        $this->assertLessThan(3, count($stack));
    }

    #[Property]
    #[CounterexampleFormatter('triggersWarning')]
    public function testFormatterThatTriggersWarning(#[ForAll('integers')] int $value): void
    {
        $this->assertLessThan(100, $value);
    }

    #[Property]
    #[CounterexampleFormatter('printsOutput')]
    public function testFormatterThatPrintsOutput(#[ForAll('integers')] int $value): void
    {
        $this->assertLessThan(100, $value);
    }

    #[Property]
    #[TestWith([50])]
    #[CounterexampleFormatter('triggersWarning')]
    public function testFormatterIsNotCalledForPropertyThatHolds(#[ForAll('integers')] int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }
}
