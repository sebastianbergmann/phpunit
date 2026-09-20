<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use function array_keys;
use function hrtime;
use Closure;
use Exception;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\AttemptErrored;
use PHPUnit\Event\Test\AttemptFailed;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestRunner\ErrorTriggered as TestRunnerErrorTriggered;
use PHPUnit\Event\TestRunner\PhpDeprecationTriggered as TestRunnerPhpDeprecationTriggered;
use PHPUnit\Event\TestSuite\Skipped as TestSuiteSkipped;
use PHPUnit\Event\TestSuite\TestSuiteWithName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TestRunner\TestResult\Issues\Issue;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(Collector::class)]
#[Small]
#[Group('test-runner')]
final class CollectorTest extends TestCase
{
    /**
     * @return non-empty-array<non-empty-string, array{Closure(Collector, Info, Test): void, Closure(TestResult): list<Issue>}>
     */
    public static function issueProvider(): array
    {
        return [
            'error' => [
                static function (Collector $collector, Info $telemetryInfo, Test $test): void
                {
                    $collector->testTriggeredError(new ErrorTriggered($telemetryInfo, $test, 'message', 'file.php', 1, false));
                },
                static fn (TestResult $result): array => $result->errors(),
            ],

            'notice' => [
                static function (Collector $collector, Info $telemetryInfo, Test $test): void
                {
                    $collector->testTriggeredNotice(new NoticeTriggered($telemetryInfo, $test, 'message', 'file.php', 1, false, false));
                },
                static fn (TestResult $result): array => $result->notices(),
            ],

            'PHP notice' => [
                static function (Collector $collector, Info $telemetryInfo, Test $test): void
                {
                    $collector->testTriggeredPhpNotice(new PhpNoticeTriggered($telemetryInfo, $test, 'message', 'file.php', 1, false, false));
                },
                static fn (TestResult $result): array => $result->phpNotices(),
            ],

            'warning' => [
                static function (Collector $collector, Info $telemetryInfo, Test $test): void
                {
                    $collector->testTriggeredWarning(new WarningTriggered($telemetryInfo, $test, 'message', 'file.php', 1, false, false));
                },
                static fn (TestResult $result): array => $result->warnings(),
            ],

            'PHP warning' => [
                static function (Collector $collector, Info $telemetryInfo, Test $test): void
                {
                    $collector->testTriggeredPhpWarning(new PhpWarningTriggered($telemetryInfo, $test, 'message', 'file.php', 1, false, false));
                },
                static fn (TestResult $result): array => $result->phpWarnings(),
            ],

            'PHP deprecation' => [
                static function (Collector $collector, Info $telemetryInfo, Test $test): void
                {
                    $collector->testTriggeredPhpDeprecation(new PhpDeprecationTriggered($telemetryInfo, $test, 'message', 'file.php', 1, false, false, false, false, IssueTrigger::from(null, null)));
                },
                static fn (TestResult $result): array => $result->phpDeprecations(),
            ],
        ];
    }

    public function testRemembersRetriedTestForFailedAttemptOfPhpt(): void
    {
        $collector = $this->collector();

        $collector->testAttemptFailed(
            new AttemptFailed(
                $this->telemetryInfo(),
                new Phpt('test.phpt'),
                ThrowableBuilder::from(new Exception('failure')),
                null,
                Duration::fromSecondsAndNanoseconds(1, 0),
            ),
        );

        $this->assertTrue($collector->result()->hasRetriedTests());
        $this->assertSame(['test.phpt' => 1], $collector->result()->retriedTests());
    }

    public function testRemembersRetriedTestForErroredAttemptOfPhpt(): void
    {
        $collector = $this->collector();

        $collector->testAttemptErrored(
            new AttemptErrored(
                $this->telemetryInfo(),
                new Phpt('test.phpt'),
                ThrowableBuilder::from(new Exception('error')),
                Duration::fromSecondsAndNanoseconds(1, 0),
            ),
        );

        $this->assertTrue($collector->result()->hasRetriedTests());
        $this->assertSame(['test.phpt' => 1], $collector->result()->retriedTests());
    }

    public function testIgnoresSkippedTestSuiteThatIsNotForTestClass(): void
    {
        $collector = $this->collector();

        $collector->testSuiteSkipped(
            new TestSuiteSkipped(
                $this->telemetryInfo(),
                new TestSuiteWithName('the-test-suite', 3, TestCollection::fromArray([])),
                'skip reason',
            ),
        );

        $result = $collector->result();

        $this->assertSame(0, $result->numberOfTestsRun());
        $this->assertSame([], $result->testSuiteSkippedEvents());
    }

    /**
     * @param Closure(Collector, Info, Test): void $trigger
     * @param Closure(TestResult): list<Issue>     $issues
     */
    #[DataProvider('issueProvider')]
    public function testRecordsSameIssueTriggeredByDifferentTestsOnce(Closure $trigger, Closure $issues): void
    {
        $collector = $this->collector();
        $one       = $this->testMethod('testOne');
        $two       = $this->testMethod('testTwo');

        $trigger($collector, $this->telemetryInfo(), $one);
        $trigger($collector, $this->telemetryInfo(), $two);

        $issues = $issues($collector->result());

        $this->assertCount(1, $issues);
        $this->assertSame([$one->id(), $two->id()], array_keys($issues[0]->triggeringTests()));
    }

    public function testDoesNotRecordErrorThatShouldNotBeProcessed(): void
    {
        $collector = $this->collector();

        $collector->testTriggeredError(
            new ErrorTriggered($this->telemetryInfo(), $this->testMethod('testOne'), 'message', 'file.php', 1, true),
        );

        $this->assertSame([], $collector->result()->errors());
    }

    public function testRecordsErrorTriggeredByTestRunner(): void
    {
        $collector = $this->collector();
        $event     = new TestRunnerErrorTriggered($this->telemetryInfo(), 'message', 'file.php', 1, false);

        $collector->testRunnerTriggeredIssueError($event);

        $this->assertSame([$event], $collector->result()->testRunnerTriggeredIssueErrorEvents());
    }

    public function testDoesNotRecordPhpDeprecationTriggeredByTestRunnerThatIsIgnoredByFilter(): void
    {
        $collector = $this->collector();

        $collector->testRunnerTriggeredIssuePhpDeprecation(
            new TestRunnerPhpDeprecationTriggered($this->telemetryInfo(), 'message', 'file.php', 1, false, false, true, IssueTrigger::from(null, null)),
        );

        $this->assertSame([], $collector->result()->testRunnerTriggeredIssuePhpDeprecationEvents());
    }

    /**
     * @param non-empty-string $methodName
     */
    private function testMethod(string $methodName): TestMethod
    {
        return new TestMethod(
            'FooTest',
            $methodName,
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('FooTest', $methodName),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }

    private function collector(): Collector
    {
        return new Collector(
            new Facade,
            new IssueFilter($this->source()),
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
}
