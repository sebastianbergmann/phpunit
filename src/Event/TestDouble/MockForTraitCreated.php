<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestDouble;

use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

final class MockForTraitCreated implements Event
{
    private Telemetry\Info $telemetryInfo;

    /**
     * @psalm-var trait-string
     */
    private string $traitName;

    /**
     * @psalm-param trait-string $traitName
     */
    public function __construct(Telemetry\Info $telemetryInfo, string $traitName)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->traitName     = $traitName;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    /**
     * @psalm-return trait-string
     */
    public function traitName(): string
    {
        return $this->traitName;
    }
}
