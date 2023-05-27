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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Filtered::class)]
#[Small]
final class FilteredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testSuite     = $this->testSuiteValueObject();

        $event = new Filtered(
            $telemetryInfo,
            $testSuite,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($testSuite, $event->testSuite());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Filtered(
            $this->telemetryInfo(),
            $this->testSuiteValueObject(),
        );

        $this->assertSame('Test Suite Filtered (9001 tests)', $event->asString());
    }
}
