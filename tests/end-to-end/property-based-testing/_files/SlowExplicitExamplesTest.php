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

use function usleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TimeLimit\TimeLimitExceededException;

final class SlowExplicitExamplesTest extends TestCase
{
    #[Property(examples: 10)]
    #[TestWith([1])]
    #[TestWith([2])]
    public function testTakesLongerThanTheTimeLimitOfTheTestRun(int $value): void
    {
        // the example is not aborted when the deadline of the test run passes,
        // so that the deadline is noticed before the next example is checked
        try {
            usleep(1500000);
        } catch (TimeLimitExceededException) {
        }

        $this->assertTrue(true);
    }

    public function testIsNotRun(): void
    {
        $this->assertTrue(true);
    }
}
