<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

final class Started implements Event
{
    private Telemetry\Info $telemetryInfo;

    private string $name;

    public function __construct(Telemetry\Info $telemetryInfo, string $name)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->name          = $name;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function name(): string
    {
        return $this->name;
    }
}
