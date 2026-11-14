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

use const PHP_INT_MAX;
use function array_filter;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class IntegerDistributionTest extends TestCase
{
    /**
     * @var array<string, list<int>>
     */
    private static array $values = [];

    /**
     * @return ValueGenerator<int>
     */
    public static function integers(): ValueGenerator
    {
        return Generators::integers();
    }

    #[Property(examples: 200)]
    public function testInt(int $value): void
    {
        self::$values['int'][] = $value;

        $this->assertIsInt($value);
    }

    /**
     * @param int<-9223372036854775807, max> $value
     */
    #[Property(examples: 200)]
    public function testIntegerRange(int $value): void
    {
        self::$values['range'][] = $value;

        $this->assertIsInt($value);
        $this->assertGreaterThanOrEqual(-PHP_INT_MAX, $value);
    }

    #[Property(examples: 200)]
    public function testGeneratorsIntegers(#[ForAll('integers')] int $value): void
    {
        self::$values['integers'][] = $value;

        $this->assertIsInt($value);
    }

    #[Depends('testInt')]
    public function testIntGeneratesNegativeNonNegativeOddAndEvenValues(): void
    {
        $this->assertGeneratesNegativeNonNegativeOddAndEvenValues(self::$values['int']);
    }

    #[Depends('testIntegerRange')]
    public function testIntegerRangeGeneratesNegativeNonNegativeOddAndEvenValues(): void
    {
        $this->assertGeneratesNegativeNonNegativeOddAndEvenValues(self::$values['range']);
    }

    #[Depends('testGeneratorsIntegers')]
    public function testGeneratorsIntegersGeneratesNegativeNonNegativeOddAndEvenValues(): void
    {
        $this->assertGeneratesNegativeNonNegativeOddAndEvenValues(self::$values['integers']);
    }

    /**
     * @param list<int> $values
     */
    private function assertGeneratesNegativeNonNegativeOddAndEvenValues(array $values): void
    {
        $this->assertNotEmpty(array_filter($values, static fn (int $value): bool => $value < 0));
        $this->assertNotEmpty(array_filter($values, static fn (int $value): bool => $value >= 0));
        $this->assertNotEmpty(array_filter($values, static fn (int $value): bool => $value % 2 !== 0));
        $this->assertNotEmpty(array_filter($values, static fn (int $value): bool => $value % 2 === 0));
    }
}
