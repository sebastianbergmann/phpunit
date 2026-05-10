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
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Finished implements Event
{
    private Telemetry\Info $telemetryInfo;
    private TestSuite $testSuite;

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Telemetry\Info $telemetryInfo, TestSuite $testSuite)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->testSuite     = $testSuite;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function testSuite(): TestSuite
    {
        return $this->testSuite;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        $prefix = 'Test Suite Finished';
        $unit   = 'test';

        if ($this->testSuite->isForTestMethodWithDataProvider()) {
            $prefix = 'Test Suite for Test Method with Data Provider Finished';
            $unit   = 'data set';
        } elseif ($this->testSuite->isForRepeatedTestMethod()) {
            $prefix = 'Test Suite for Repeated Test Method Finished';
            $unit   = 'repetition';
        }

        $count  = $this->testSuite->count();
        $plural = '';

        if ($count !== 1) {
            $plural = 's';
        }

        return sprintf(
            '%s (%s, %d %s%s)',
            $prefix,
            $this->testSuite->name(),
            $count,
            $unit,
            $plural,
        );
    }
}
