<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Extension;

use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

final class Loaded implements Event
{
    private Telemetry\Info $telemetryInfo;

    private string $name;

    private string $version;

    public function __construct(Telemetry\Info $telemetryInfo, string $name, string $version)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->name          = $name;
        $this->version       = $version;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->version;
    }
}
