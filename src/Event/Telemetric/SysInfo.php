<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetric;

final class SysInfo
{
    private Clock $clock;

    private MemInfo $memInfo;

    public function __construct(Clock $clock, MemInfo $memInfo)
    {
        $this->clock   = $clock;
        $this->memInfo = $memInfo;
    }

    public function snapshot(): Snapshot
    {
        return new SnapShot(
            $this->clock->now(),
            $this->memInfo->usage(),
            $this->memInfo->peak()
        );
    }
}
