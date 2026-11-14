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

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FalsifiedPropertiesTest extends TestCase
{
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

    #[Property]
    public function testIsFalsifiedFromFive(#[ForAll('digits')] int $value): void
    {
        $this->assertLessThan(5, $value);
    }

    #[Property]
    public function testIsFalsifiedOnlyTheFirstTime(#[ForAll('digits')] int $value): void
    {
        self::$numberOfRuns++;

        $this->assertGreaterThan(1, self::$numberOfRuns);
    }

    #[Property]
    public function testIsFalsifiedInTwoWays(#[ForAll('digits')] int $value): void
    {
        if ($value >= 3 && $value < 5) {
            throw new RuntimeException('error');
        }

        $this->assertLessThan(5, $value);
    }
}
