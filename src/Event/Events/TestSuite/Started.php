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

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
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

    public function asString(): string
    {
        $name = '';

        if (!empty($this->name)) {
            $name = sprintf(
                '(%s)',
                $this->name
            );
        }

        return sprintf(
            '%s Test Suite Started %s',
            $this->telemetryInfo()->asString(),
            $name,
        );
    }
}
