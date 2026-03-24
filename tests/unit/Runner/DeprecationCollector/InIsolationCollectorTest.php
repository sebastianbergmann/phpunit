<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\DeprecationCollector;

use function hrtime;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(InIsolationCollector::class)]
#[Small]
#[Group('test-runner')]
final class InIsolationCollectorTest extends TestCase
{
    public function testInitiallyHasNoDeprecations(): void
    {
        $collector = new InIsolationCollector(
            new IssueFilter($this->source()),
        );

        $this->assertSame([], $collector->deprecations());
        $this->assertSame([], $collector->filteredDeprecations());
    }

    public function testCollectsDeprecation(): void
    {
        $collector = new InIsolationCollector(
            new IssueFilter($this->source()),
        );

        $collector->testTriggeredDeprecation($this->deprecationEvent('deprecation message'));

        $this->assertSame(['deprecation message'], $collector->deprecations());
    }

    public function testAddsToFilteredDeprecationsWhenIssueFilterAccepts(): void
    {
        $collector = new InIsolationCollector(
            new IssueFilter($this->source()),
        );

        $collector->testTriggeredDeprecation($this->deprecationEvent('deprecation message'));

        $this->assertSame(['deprecation message'], $collector->filteredDeprecations());
    }

    public function testDoesNotAddToFilteredDeprecationsWhenIssueFilterRejects(): void
    {
        $collector = new InIsolationCollector(
            new IssueFilter($this->source()),
        );

        $collector->testTriggeredDeprecation($this->deprecationEvent('deprecation message', suppressed: true));

        $this->assertSame(['deprecation message'], $collector->deprecations());
        $this->assertSame([], $collector->filteredDeprecations());
    }

    public function testCollectsMultipleDeprecations(): void
    {
        $collector = new InIsolationCollector(
            new IssueFilter($this->source()),
        );

        $collector->testTriggeredDeprecation($this->deprecationEvent('first'));
        $collector->testTriggeredDeprecation($this->deprecationEvent('second'));

        $this->assertSame(['first', 'second'], $collector->deprecations());
        $this->assertSame(['first', 'second'], $collector->filteredDeprecations());
    }

    private function deprecationEvent(string $message, bool $suppressed = false): DeprecationTriggered
    {
        return new DeprecationTriggered(
            $this->telemetryInfo(),
            $this->testValueObject(),
            $message,
            'file.php',
            1,
            $suppressed,
            false,
            false,
            IssueTrigger::from(null, null),
            'stack trace',
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
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
        );
    }

    private function testValueObject(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
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
                'functions' => [],
                'methods'   => [],
            ],
            false,
            false,
            false,
            false,
        );
    }
}
