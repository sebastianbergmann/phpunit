<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\GlobalState;

use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;
use SebastianBergmann\GlobalState\Snapshot;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Restored implements Event
{
    private Telemetry\Info $telemetryInfo;
    private Snapshot $snapshot;

    public function __construct(Telemetry\Info $telemetryInfo, Snapshot $snapshot)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->snapshot      = $snapshot;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function snapshot(): Snapshot
    {
        return $this->snapshot;
    }

    public function asString(): string
    {
        return 'Global State Restored';
    }
}
