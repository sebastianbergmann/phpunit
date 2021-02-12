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

final class Modified implements Event
{
    private Telemetry\Info $telemetryInfo;

    private Snapshot $snapshotBefore;

    private Snapshot $snapshotAfter;

    private string $message;

    public function __construct(
        Telemetry\Info $telemetryInfo,
        Snapshot $snapshotBefore,
        Snapshot $snapshotAfter,
        string $message
    ) {
        $this->telemetryInfo  = $telemetryInfo;
        $this->snapshotBefore = $snapshotBefore;
        $this->snapshotAfter  = $snapshotAfter;
        $this->message        = $message;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function snapshotBefore(): Snapshot
    {
        return $this->snapshotBefore;
    }

    public function snapshotAfter(): Snapshot
    {
        return $this->snapshotAfter;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function asString(): string
    {
        return '';
    }
}
