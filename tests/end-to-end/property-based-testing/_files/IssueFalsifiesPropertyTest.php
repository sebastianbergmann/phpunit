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
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class IssueFalsifiesPropertyTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function integers(): ValueGenerator
    {
        return Generators::integers(0, 1000);
    }

    #[Property]
    public function testTriggersWarningForLargeValue(#[ForAll('integers')] int $value): void
    {
        if ($value >= 100) {
            trigger_error('value is too large', E_USER_WARNING);
        }

        $this->assertGreaterThanOrEqual(0, $value);
    }

    #[Property]
    public function testTriggersWarningForLargeValueWithoutPerformingAssertions(#[ForAll('integers')] int $value): void
    {
        if ($value >= 100) {
            trigger_error('value is too large', E_USER_WARNING);
        }
    }

    #[Property]
    public function testTriggersSuppressedWarningForLargeValue(#[ForAll('integers')] int $value): void
    {
        if ($value >= 100) {
            @trigger_error('value is too large', E_USER_WARNING);
        }

        $this->assertGreaterThanOrEqual(0, $value);
    }

    #[Property]
    #[TestWith([500])]
    public function testTriggersWarningForExplicitExample(#[ForAll('integers')] int $value): void
    {
        if ($value === 500) {
            trigger_error('value is 500', E_USER_WARNING);
        }

        $this->assertGreaterThanOrEqual(0, $value);
    }

    #[Property]
    public function testTriggersWarningBeforeExampleIsRejected(#[ForAll('integers')] int $value): void
    {
        if ($value >= 100) {
            trigger_error('value is too large', E_USER_WARNING);
        }

        $this->assume($value < 100);

        $this->assertGreaterThanOrEqual(0, $value);
    }

    #[Property]
    public function testTriggersWarningBeforeExampleIsSkipped(#[ForAll('integers')] int $value): void
    {
        trigger_error('example is skipped', E_USER_WARNING);

        $this->markTestSkipped('skipped');
    }
}
