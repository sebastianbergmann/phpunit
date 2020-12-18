<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class AbstractEventTestCase extends TestCase
{
    final protected static function createTelemetryInfo(): Telemetry\Info
    {
        return new Telemetry\Info(
            new Telemetry\Snapshot(
                new DateTimeImmutable('now'),
                Telemetry\MemoryUsage::fromBytes(1000),
                Telemetry\MemoryUsage::fromBytes(2000)
            ),
            new DateInterval('P2D'),
            Telemetry\MemoryUsage::fromBytes(2000),
            new DateInterval('P1D'),
            Telemetry\MemoryUsage::fromBytes(3000)
        );
    }
}
