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

use function hrtime;
use function usleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class SlowFinalReplayTest extends TestCase
{
    private static ?int $start = null;

    #[Property(examples: 10)]
    public function testTakesLongerToReplayTheCounterexampleThanTheTimeLimitOfTheTestRun(int $value): void
    {
        if (self::$start === null) {
            self::$start = hrtime(true);
        }

        if ($value < 1000) {
            $this->assertLessThan(1000, $value);

            return;
        }

        // the final replay of the counterexample starts after the deadline of
        // the test run has passed, which interrupts shrinking
        if (hrtime(true) - self::$start > 1000000000) {
            usleep(3000000);
        } else {
            usleep(300000);
        }

        $this->assertLessThan(1000, $value);
    }
}
