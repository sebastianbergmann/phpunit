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
use function trigger_error;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class IssueTriggeredOnlyOnceTest extends TestCase
{
    private static bool $triggeredForPropertyThatHolds                  = false;
    private static bool $triggeredForPropertyThatDoesNotHold            = false;
    private static bool $triggeredForPropertyThatTriggersAnotherWarning = false;

    /**
     * @return ValueGenerator<int>
     */
    public static function integers(): ValueGenerator
    {
        return Generators::integers(0, 1000);
    }

    /**
     * @return ValueGenerator<int>
     */
    public static function largeIntegers(): ValueGenerator
    {
        return Generators::integers(100, 1000);
    }

    #[Property]
    public function testTriggersWarningOnlyForTheFirstExample(#[ForAll('integers')] int $value): void
    {
        if (!self::$triggeredForPropertyThatHolds) {
            self::$triggeredForPropertyThatHolds = true;

            trigger_error('triggered only once', E_USER_WARNING);
        }

        $this->assertGreaterThanOrEqual(0, $value);
    }

    #[Property]
    public function testTriggersWarningOnlyForTheFirstExampleAndFailsForLargeValue(#[ForAll('integers')] int $value): void
    {
        if (!self::$triggeredForPropertyThatDoesNotHold) {
            self::$triggeredForPropertyThatDoesNotHold = true;

            trigger_error('triggered only once', E_USER_WARNING);
        }

        $this->assertLessThan(100, $value);
    }

    #[Property]
    public function testTriggersWarningOnlyForTheFirstExampleAndAnotherWarningForLargeValue(#[ForAll('largeIntegers')] int $value): void
    {
        if (!self::$triggeredForPropertyThatTriggersAnotherWarning) {
            self::$triggeredForPropertyThatTriggersAnotherWarning = true;

            trigger_error('triggered only once', E_USER_WARNING);
        }

        if ($value >= 100) {
            trigger_error('value is too large', E_USER_WARNING);
        }

        $this->assertGreaterThanOrEqual(0, $value);
    }
}
