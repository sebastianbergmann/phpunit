<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Event\Tracer;

use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\DurationFormatter;

final class LogDurationFormatter implements DurationFormatter
{
    public function format(Duration $duration): string
    {
        $seconds = $duration->seconds();
        $minutes = 00;
        $hours   = 00;

        if ($seconds > 60 * 60) {
            $hours = floor($seconds / 60 / 60);
            $seconds -= ($hours * 60 * 60);
        }

        if ($seconds > 60) {
            $minutes = floor($seconds / 60);
            $seconds -= ($minutes * 60);
        }

        return sprintf(
            '%02d:%02d:%02d.%09d',
            $hours,
            $minutes,
            $seconds,
            $duration->nanoseconds()
        );
    }
}
