<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function hrtime;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestFixture\TestDoxTest;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(TestTriggeredPhpunitWarningSubscriber::class)]
#[Group('testdox')]
#[Small]
final class TestTriggeredPhpunitWarningSubscriberTest extends TestCase
{
    public function test_Subscriber_forwards_event_to_collector(): void
    {
        $collector = new TestResultCollector(new Facade, new IssueFilter($this->source()));
        $test      = $this->testMethod();

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $test));

        new TestTriggeredPhpunitWarningSubscriber($collector)->notify(
            new PhpunitWarningTriggered($this->telemetryInfo(), $test, 'message', false),
        );

        $collector->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $this->assertTrue(
            $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray()[0]->status()->isWarning(),
        );
    }

    private function testMethod(): TestMethod
    {
        return new TestMethod(
            TestDoxTest::class,
            'testOne',
            TestDoxTest::class . '.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName(TestDoxTest::class, 'testOne'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }

    private function telemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000),
                new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
        );
    }

    private function source(): Source
    {
        return new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions'               => [],
                'methods'                 => [],
                'ignoreUndefinedTriggers' => true,
            ],
            false,
            false,
            false,
            false,
        );
    }
}
