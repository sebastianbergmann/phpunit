<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetry;

use function hrtime;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Telemetry\SystemStopWatch
 */
final class SystemStopWatchTest extends TestCase
{
    public function testNowReturnsDateTimeImmutable(): void
    {
        $clock = new SystemStopWatch();

        $before = new HRTime(...hrtime(false));

        $current = $clock->current();

        $after = new HRTime(...hrtime(false));

        $durationBetweenCurrentAndBefore = $current->duration($before);

        $this->assertSame(0, $durationBetweenCurrentAndBefore->seconds());
        $this->assertGreaterThan(0, $durationBetweenCurrentAndBefore->nanoseconds());

        $durationBetweenAfterAndCurrent = $after->duration($current);

        $this->assertSame(0, $durationBetweenAfterAndCurrent->seconds());
        $this->assertGreaterThan(0, $durationBetweenAfterAndCurrent->nanoseconds());
    }
}
