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

    private Info $info;

    public function __construct(Telemetry\Info $telemetryInfo, Info $info)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->info          = $info;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function testSuiteInfo(): Info
    {
        return $this->info;
    }

    public function asString(): string
    {
        $name = '';

        if (!empty($this->info->name())) {
            $name = $this->info->name() . ', ';
        }

        return sprintf(
            'Test Suite Started (%s%d tests)',
            $name,
            $this->info->count()
        );
    }
}
