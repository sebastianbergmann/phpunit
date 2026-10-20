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

final class PartiallyReproducedFailureTest extends TestCase
{
    private static int $numberOfCalls = 0;

    #[Property(examples: 10)]
    public function testFailsForLargeValuesInEverySecondCall(int $value): void
    {
        self::$numberOfCalls++;

        if (self::$numberOfCalls % 2 === 0) {
            $this->assertLessThan(100, $value, 'large values fail in every second call');
        }

        $this->assertIsInt($value);
    }
}
