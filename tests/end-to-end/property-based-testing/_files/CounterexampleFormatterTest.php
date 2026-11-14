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
use PHPUnit\Framework\Attributes\CounterexampleFormatterExternal;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CounterexampleFormatterTest extends TestCase
{
    public static function asSum(int $a, int $b): string
    {
        return sprintf("%d + %d\nis greater than 10", $a, $b);
    }

    public static function raises(int $a, int $b): string
    {
        throw new RuntimeException('message');
    }

    public static function returnsAnInteger(int $a, int $b): int
    {
        return $a + $b;
    }

    public static function returnsAnEmptyString(int $a, int $b): string
    {
        return '';
    }

    /**
     * @param int<0, 100> $a
     * @param int<0, 100> $b
     */
    #[Property]
    #[CounterexampleFormatter('asSum')]
    public function testFormatterOfTestClass(int $a, int $b): void
    {
        $this->assertLessThanOrEqual(10, $a + $b);
    }

    /**
     * @param int<0, 100> $a
     * @param int<0, 100> $b
     */
    #[Property]
    #[CounterexampleFormatterExternal(CounterexampleFormatters::class, 'asProduct')]
    public function testFormatterOfAnotherClass(int $a, int $b): void
    {
        $this->assertLessThanOrEqual(10, $a + $b);
    }

    /**
     * @param int<0, 100> $a
     * @param int<0, 100> $b
     */
    #[Property]
    #[TestWith([20, 30])]
    #[CounterexampleFormatter('asSum')]
    public function testFormatterForExplicitExample(int $a, int $b): void
    {
        $this->assertLessThanOrEqual(10, $a + $b);
    }

    /**
     * @param int<0, 100> $a
     * @param int<0, 100> $b
     */
    #[Property]
    #[CounterexampleFormatter('raises')]
    public function testFormatterThatRaisesAnException(int $a, int $b): void
    {
        $this->assertLessThanOrEqual(10, $a + $b);
    }

    /**
     * @param int<0, 100> $a
     * @param int<0, 100> $b
     */
    #[Property]
    #[CounterexampleFormatter('returnsAnInteger')]
    public function testFormatterThatDoesNotReturnAString(int $a, int $b): void
    {
        $this->assertLessThanOrEqual(10, $a + $b);
    }

    /**
     * @param int<0, 100> $a
     * @param int<0, 100> $b
     */
    #[Property]
    #[CounterexampleFormatter('returnsAnEmptyString')]
    public function testFormatterThatReturnsAnEmptyString(int $a, int $b): void
    {
        $this->assertLessThanOrEqual(10, $a + $b);
    }

    /**
     * @param int<0, 100> $a
     * @param int<0, 100> $b
     */
    #[Property]
    #[CounterexampleFormatter('asSum')]
    public function testFormatterOfPropertyThatHolds(int $a, int $b): void
    {
        $this->assertGreaterThanOrEqual(0, $a + $b);
    }
}

final class CounterexampleFormatters
{
    public static function asProduct(int $a, int $b): string
    {
        return sprintf('%d * %d', $a, $b);
    }
}
