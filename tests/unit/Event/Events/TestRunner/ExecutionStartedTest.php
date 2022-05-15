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

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ExecutionStarted::class)]
final class ExecutionStartedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testSuite     = $this->testSuiteValueObject();

        $event = new ExecutionStarted($telemetryInfo, $testSuite);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testSuite     = $this->testSuiteValueObject();

        $event = new ExecutionStarted($telemetryInfo, $testSuite);

        $this->assertSame('Test Runner Execution Started (9001 tests)', $event->asString());
    }
}
