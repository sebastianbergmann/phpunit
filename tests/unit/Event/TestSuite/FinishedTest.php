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

use PHPUnit\Event\AbstractEventTestCase;
use SebastianBergmann\CodeCoverage;

/**
 * @covers \PHPUnit\Event\TestSuite\Finished
 */
final class FinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValuesWhenCodeCoverageIsNull(): void
    {
        $telemetryInfo = self::createTelemetryInfo();
        $name          = 'foo';
        $result        = new Result(
            5,
            new FailureCollection(),
            new FailureCollection(),
            new FailureCollection(),
            new FailureCollection(),
            new FailureCollection(),
            new FailureCollection(),
            [],
            []
        );
        $codeCoverage = null;

        $event = new Finished(
            $telemetryInfo,
            $name,
            $result,
            $codeCoverage
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($name, $event->name());
        $this->assertSame($result, $event->result());
        $this->assertSame($codeCoverage, $event->codeCoverage());
    }

    public function testConstructorSetsValuesWhenCodeCoverageIsNotNull(): void
    {
        $telemetryInfo = self::createTelemetryInfo();
        $name          = 'foo';
        $result        = new Result(
            5,
            new FailureCollection(),
            new FailureCollection(),
            new FailureCollection(),
            new FailureCollection(),
            new FailureCollection(),
            new FailureCollection(),
            [],
            []
        );
        $codeCoverage = new CodeCoverage\CodeCoverage(
            $this->createMock(CodeCoverage\Driver\Driver::class),
            new CodeCoverage\Filter()
        );

        $event = new Finished(
            $telemetryInfo,
            $name,
            $result,
            $codeCoverage
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($name, $event->name());
        $this->assertSame($result, $event->result());
        $this->assertSame($codeCoverage, $event->codeCoverage());
    }
}
