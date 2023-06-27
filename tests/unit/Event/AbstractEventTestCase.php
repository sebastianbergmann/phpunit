<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use function hrtime;
use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestSuite\TestSuiteWithName;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;

abstract class AbstractEventTestCase extends TestCase
{
    final protected function telemetryInfo(): Telemetry\Info
    {
        return new Telemetry\Info(
            new Telemetry\Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                Telemetry\MemoryUsage::fromBytes(1000),
                Telemetry\MemoryUsage::fromBytes(2000),
                new Telemetry\GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            Telemetry\MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            Telemetry\MemoryUsage::fromBytes(3000),
        );
    }

    final protected function testValueObject(): Code\TestMethod
    {
        return new Code\TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }

    final protected function testSuiteValueObject(): TestSuiteWithName
    {
        return new TestSuiteWithName(
            'foo',
            9001,
            TestCollection::fromArray([]),
        );
    }
}
