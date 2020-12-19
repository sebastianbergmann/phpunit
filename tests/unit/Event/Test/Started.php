<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use PHPUnit\Event\Application\Runtime;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

final class Started implements Event
{
    private Telemetry\Info $telemetryInfo;

    private Runtime $runtime;

    public function __construct(Telemetry\Info $telemetryInfo, Runtime $runtime)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->runtime       = $runtime;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function runtime(): Runtime
    {
        return $this->runtime;
    }
}
