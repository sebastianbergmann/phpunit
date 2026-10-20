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

use function assert;
use function is_float;
use function microtime;
use function usleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class SlowFinalReplayTest extends TestCase
{
    #[Property(examples: 10)]
    public function testTakesLongerToReplayTheCounterexampleThanTheTimeLimitOfTheTestRun(int $value): void
    {
        if ($value < 1000) {
            $this->assertLessThan(1000, $value);

            return;
        }

        // the final replay of the counterexample starts after the deadline of
        // the test run has passed, which interrupts shrinking; the test run
        // starts after the process, so more than a second has passed since
        // the process started when the deadline has passed
        $start = $_SERVER['REQUEST_TIME_FLOAT'];

        assert(is_float($start));

        if (microtime(true) - $start > 1.0) {
            usleep(3000000);
        } else {
            usleep(300000);
        }

        $this->assertLessThan(1000, $value);
    }
}
