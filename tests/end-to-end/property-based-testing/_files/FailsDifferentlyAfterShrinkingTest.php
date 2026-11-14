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

use LogicException;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FailsDifferentlyAfterShrinkingTest extends TestCase
{
    private static int $numberOfCallsWithOneHundred = 0;

    #[Property(examples: 10)]
    public function testFailsDifferentlyWhenItIsCalledWithOneHundredForTheSecondTime(int $value): void
    {
        if ($value === 100) {
            self::$numberOfCallsWithOneHundred++;
        }

        if ($value === 100 && self::$numberOfCallsWithOneHundred > 1) {
            throw new LogicException('one hundred, for the second time');
        }

        if ($value >= 100) {
            throw new RuntimeException('large value');
        }

        $this->assertIsInt($value);
    }
}
