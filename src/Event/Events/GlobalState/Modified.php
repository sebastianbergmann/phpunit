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

use const PHP_EOL;
use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;
use SebastianBergmann\GlobalState\Snapshot;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Modified implements Event
{
    private Telemetry\Info $telemetryInfo;
    private Snapshot $snapshotBefore;
    private Snapshot $snapshotAfter;
    private string $diff;

    public function __construct(Telemetry\Info $telemetryInfo, Snapshot $snapshotBefore, Snapshot $snapshotAfter, string $diff)
    {
        $this->telemetryInfo  = $telemetryInfo;
        $this->snapshotBefore = $snapshotBefore;
        $this->snapshotAfter  = $snapshotAfter;
        $this->diff           = $diff;
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

    public function diff(): string
    {
        return $this->diff;
    }

    public function asString(): string
    {
        return sprintf(
            'Global State Modified%s%s',
            PHP_EOL,
            $this->diff
        );
    }
}
