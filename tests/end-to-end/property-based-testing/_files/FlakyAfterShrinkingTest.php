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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class FlakyAfterShrinkingTest extends TestCase
{
    private static int $numberOfCalls = 0;

    #[Property(examples: 10)]
    public function testFailsForLargeValuesUntilItWasCalledTwelveTimes(int $value): void
    {
        self::$numberOfCalls++;

        if (self::$numberOfCalls < 12) {
            $this->assertLessThan(100, $value, 'large values fail until the property was called twelve times');
        }

        $this->assertIsInt($value);
    }
}
