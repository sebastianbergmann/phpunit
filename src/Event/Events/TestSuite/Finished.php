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
final class Finished implements Event
{
    private Telemetry\Info $telemetryInfo;
    private TestSuite $testSuite;
    private Result $result;

    public function __construct(Telemetry\Info $telemetryInfo, TestSuite $testSuite, Result $result)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->testSuite     = $testSuite;
        $this->result        = $result;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function testSuite(): TestSuite
    {
        return $this->testSuite;
    }

    public function result(): Result
    {
        return $this->result;
    }

    public function asString(): string
    {
        $name = '';

        if (!empty($this->testSuite->name())) {
            $name = $this->testSuite->name() . ', ';
        }

        return sprintf(
            'Test Suite Finished (%s%d test%s)',
            $name,
            $this->testSuite->count(),
            $this->testSuite->count() !== 1 ? 's' : ''
        );
    }
}
