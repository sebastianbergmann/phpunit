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

use function array_keys;
use function hrtime;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestFixture\InheritanceA;
use PHPUnit\TestFixture\TestDoxTest;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(TestResultCollector::class)]
#[Group('testdox')]
#[Small]
final class TestResultCollectorTest extends TestCase
{
    public function test_No_test_results_are_collected_before_any_event_is_observed(): void
    {
        $this->assertSame([], $this->collector()->testMethodsGroupedByClass());
    }

    public function test_Test_method_that_passes_is_recorded_as_successful(): void
    {
        $collector = $this->collector();
        $test      = $this->testMethod('testOne');

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $collector->testPassed(new Passed($this->telemetryInfo(), $test));
        $collector->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $results = $collector->testMethodsGroupedByClass();

        $this->assertSame([TestDoxTest::class], array_keys($results));
        $first = $results[TestDoxTest::class]->asArray()[0];
        $this->assertTrue($first->status()->isSuccess());
        $this->assertFalse($first->hasThrowable());
    }

    public function test_Test_method_that_fails_is_recorded_with_throwable(): void
    {
        $collector = $this->collector();
        $test      = $this->testMethod('testOne');
        $throwable = $this->throwable('failure message');

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $collector->testFailed(new Failed($this->telemetryInfo(), $test, $throwable, null));
        $collector->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $first = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray()[0];
        $this->assertTrue($first->status()->isFailure());
        $this->assertTrue($first->hasThrowable());
        $this->assertSame($throwable, $first->throwable());
    }

    public function test_Test_method_that_errors_after_being_prepared_is_recorded_on_finish(): void
    {
        $collector = $this->collector();
        $test      = $this->testMethod('testOne');

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $collector->testErrored(new Errored($this->telemetryInfo(), $test, $this->throwable('boom')));
        $collector->testFinished(new Finished($this->telemetryInfo(), $test, 0));

        $results = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray();
        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->status()->isError());
    }

    public function test_Test_method_that_errors_before_being_prepared_is_recorded_immediately(): void
    {
        $collector = $this->collector();
        $test      = $this->testMethod('testOne');

        $collector->testErrored(new Errored($this->telemetryInfo(), $test, $this->throwable('early boom')));

        $results = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray();
        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->status()->isError());
    }

    public function test_Skipped_marked_incomplete_and_risky_test_methods_are_recorded_with_their_status(): void
    {
        $collector = $this->collector();

        $skipped = $this->testMethod('testOne');
        $collector->testPrepared(new Prepared($this->telemetryInfo(), $skipped));
        $collector->testSkipped(new Skipped($this->telemetryInfo(), $skipped, 'skip reason'));
        $collector->testFinished(new Finished($this->telemetryInfo(), $skipped, 0));

        $incomplete = $this->testMethod('testTwo');
        $collector->testPrepared(new Prepared($this->telemetryInfo(), $incomplete));
        $collector->testMarkedIncomplete(new MarkedIncomplete($this->telemetryInfo(), $incomplete, $this->throwable('incomplete')));
        $collector->testFinished(new Finished($this->telemetryInfo(), $incomplete, 0));

        $risky = $this->testMethod('testThree');
        $collector->testPrepared(new Prepared($this->telemetryInfo(), $risky));
        $collector->testConsideredRisky(new ConsideredRisky($this->telemetryInfo(), $risky, 'risky reason'));
        $collector->testFinished(new Finished($this->telemetryInfo(), $risky, 0));

        $results = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray();
        $this->assertCount(3, $results);
        $this->assertTrue($results[0]->status()->isSkipped());
        $this->assertTrue($results[1]->status()->isIncomplete());
        $this->assertTrue($results[2]->status()->isRisky());
    }

    public function test_Test_method_that_triggers_PHPUnit_deprecation_error_or_warning_is_recorded_with_matching_status(): void
    {
        $collector = $this->collector();

        $deprecation = $this->testMethod('testOne');
        $collector->testPrepared(new Prepared($this->telemetryInfo(), $deprecation));
        $collector->testTriggeredPhpunitDeprecation(new PhpunitDeprecationTriggered($this->telemetryInfo(), $deprecation, 'message'));
        $collector->testFinished(new Finished($this->telemetryInfo(), $deprecation, 1));

        $error = $this->testMethod('testTwo');
        $collector->testPrepared(new Prepared($this->telemetryInfo(), $error));
        $collector->testTriggeredPhpunitError(new PhpunitErrorTriggered($this->telemetryInfo(), $error, 'message'));
        $collector->testFinished(new Finished($this->telemetryInfo(), $error, 1));

        $warning = $this->testMethod('testThree');
        $collector->testPrepared(new Prepared($this->telemetryInfo(), $warning));
        $collector->testTriggeredPhpunitWarning(new PhpunitWarningTriggered($this->telemetryInfo(), $warning, 'message', false));
        $collector->testFinished(new Finished($this->telemetryInfo(), $warning, 1));

        $results = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray();
        $this->assertTrue($results[0]->status()->isDeprecation());
        $this->assertTrue($results[1]->status()->isError());
        $this->assertTrue($results[2]->status()->isWarning());
    }

    public function test_PHPUnit_warning_ignored_by_test_does_not_change_status(): void
    {
        $collector = $this->collector();
        $test      = $this->testMethod('testOne');

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $collector->testPassed(new Passed($this->telemetryInfo(), $test));
        $collector->testTriggeredPhpunitWarning(new PhpunitWarningTriggered($this->telemetryInfo(), $test, 'message', true));
        $collector->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $first = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray()[0];
        $this->assertTrue($first->status()->isSuccess());
    }

    public function test_Test_method_that_triggers_a_deprecation_notice_or_warning_is_recorded_with_matching_status(): void
    {
        $collector = $this->collector();
        $info      = $this->telemetryInfo();
        $trigger   = IssueTrigger::from(null, null);

        $deprecation = $this->testMethod('testOne');
        $collector->testPrepared(new Prepared($info, $deprecation));
        $collector->testTriggeredDeprecation(new DeprecationTriggered($info, $deprecation, 'message', 'file.php', 1, false, false, false, $trigger, 'trace'));
        $collector->testFinished(new Finished($info, $deprecation, 1));

        $notice = $this->testMethod('testTwo');
        $collector->testPrepared(new Prepared($info, $notice));
        $collector->testTriggeredNotice(new NoticeTriggered($info, $notice, 'message', 'file.php', 1, false, false));
        $collector->testFinished(new Finished($info, $notice, 1));

        $warning = $this->testMethod('testThree');
        $collector->testPrepared(new Prepared($info, $warning));
        $collector->testTriggeredWarning(new WarningTriggered($info, $warning, 'message', 'file.php', 1, false, false));
        $collector->testFinished(new Finished($info, $warning, 1));

        $phpDeprecation = $this->testMethod('testFour');
        $collector->testPrepared(new Prepared($info, $phpDeprecation));
        $collector->testTriggeredPhpDeprecation(new PhpDeprecationTriggered($info, $phpDeprecation, 'message', 'file.php', 1, false, false, false, $trigger));
        $collector->testFinished(new Finished($info, $phpDeprecation, 1));

        $phpNotice = $this->testMethod('testFive');
        $collector->testPrepared(new Prepared($info, $phpNotice));
        $collector->testTriggeredPhpNotice(new PhpNoticeTriggered($info, $phpNotice, 'message', 'file.php', 1, false, false));
        $collector->testFinished(new Finished($info, $phpNotice, 1));

        $phpWarning = $this->testMethod('testSix');
        $collector->testPrepared(new Prepared($info, $phpWarning));
        $collector->testTriggeredPhpWarning(new PhpWarningTriggered($info, $phpWarning, 'message', 'file.php', 1, false, false));
        $collector->testFinished(new Finished($info, $phpWarning, 1));

        $results = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray();
        $this->assertTrue($results[0]->status()->isDeprecation());
        $this->assertTrue($results[1]->status()->isNotice());
        $this->assertTrue($results[2]->status()->isWarning());
        $this->assertTrue($results[3]->status()->isDeprecation());
        $this->assertTrue($results[4]->status()->isNotice());
        $this->assertTrue($results[5]->status()->isWarning());
    }

    public function test_Status_is_not_demoted_to_a_less_important_status(): void
    {
        $collector = $this->collector();
        $test      = $this->testMethod('testOne');

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $collector->testFailed(new Failed($this->telemetryInfo(), $test, $this->throwable('boom'), null));
        $collector->testTriggeredPhpunitDeprecation(new PhpunitDeprecationTriggered($this->telemetryInfo(), $test, 'message'));
        $collector->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $first = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray()[0];
        $this->assertTrue($first->status()->isFailure());
    }

    public function test_Issue_triggered_for_non_test_method_is_ignored(): void
    {
        $collector = $this->collector();
        $test      = $this->testMethod('testOne');

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $collector->testTriggeredDeprecation(
            new DeprecationTriggered(
                $this->telemetryInfo(),
                $test,
                'message',
                'file.php',
                1,
                false,
                false,
                true,
                IssueTrigger::from(null, null),
                'trace',
            ),
        );
        $collector->testPassed(new Passed($this->telemetryInfo(), $test));
        $collector->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $first = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray()[0];
        $this->assertTrue($first->status()->isSuccess());
    }

    public function test_Issue_ignored_by_baseline_does_not_change_status(): void
    {
        $collector = $this->collector();
        $test      = $this->testMethod('testOne');

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $collector->testTriggeredDeprecation(
            new DeprecationTriggered($this->telemetryInfo(), $test, 'message', 'file.php', 1, false, true, false, IssueTrigger::from(null, null), 'trace'),
        );
        $collector->testTriggeredNotice(
            new NoticeTriggered($this->telemetryInfo(), $test, 'message', 'file.php', 1, false, true),
        );
        $collector->testTriggeredWarning(
            new WarningTriggered($this->telemetryInfo(), $test, 'message', 'file.php', 1, false, true),
        );
        $collector->testTriggeredPhpDeprecation(
            new PhpDeprecationTriggered($this->telemetryInfo(), $test, 'message', 'file.php', 1, false, true, false, IssueTrigger::from(null, null)),
        );
        $collector->testTriggeredPhpNotice(
            new PhpNoticeTriggered($this->telemetryInfo(), $test, 'message', 'file.php', 1, false, true),
        );
        $collector->testTriggeredPhpWarning(
            new PhpWarningTriggered($this->telemetryInfo(), $test, 'message', 'file.php', 1, false, true),
        );
        $collector->testPassed(new Passed($this->telemetryInfo(), $test));
        $collector->testFinished(new Finished($this->telemetryInfo(), $test, 1));

        $first = $collector->testMethodsGroupedByClass()[TestDoxTest::class]->asArray()[0];
        $this->assertTrue($first->status()->isSuccess());
    }

    public function test_Test_classes_in_the_result_are_sorted_alphabetically_by_prettified_class_name(): void
    {
        $collector = $this->collector();

        $first  = $this->testMethod('testOne', TestDoxTest::class);
        $second = $this->testMethod('testSomething', InheritanceA::class);

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $first));
        $collector->testPassed(new Passed($this->telemetryInfo(), $first));
        $collector->testFinished(new Finished($this->telemetryInfo(), $first, 1));

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $second));
        $collector->testPassed(new Passed($this->telemetryInfo(), $second));
        $collector->testFinished(new Finished($this->telemetryInfo(), $second, 1));

        $this->assertSame(
            [InheritanceA::class, TestDoxTest::class],
            array_keys($collector->testMethodsGroupedByClass()),
        );
    }

    public function test_Events_for_a_non_test_method_are_ignored(): void
    {
        $collector = $this->collector();
        $phpt      = new Phpt('Foo.phpt');
        $throwable = $this->throwable('boom');

        $collector->testPrepared(new Prepared($this->telemetryInfo(), $phpt));
        $collector->testErrored(new Errored($this->telemetryInfo(), $phpt, $throwable));
        $collector->testFailed(new Failed($this->telemetryInfo(), $phpt, $throwable, null));
        $collector->testPassed(new Passed($this->telemetryInfo(), $phpt));
        $collector->testSkipped(new Skipped($this->telemetryInfo(), $phpt, ''));
        $collector->testMarkedIncomplete(new MarkedIncomplete($this->telemetryInfo(), $phpt, $throwable));
        $collector->testConsideredRisky(new ConsideredRisky($this->telemetryInfo(), $phpt, 'reason'));
        $collector->testTriggeredPhpunitDeprecation(new PhpunitDeprecationTriggered($this->telemetryInfo(), $phpt, 'message'));
        $collector->testTriggeredPhpunitError(new PhpunitErrorTriggered($this->telemetryInfo(), $phpt, 'message'));
        $collector->testTriggeredPhpunitWarning(new PhpunitWarningTriggered($this->telemetryInfo(), $phpt, 'message', false));
        $collector->testFinished(new Finished($this->telemetryInfo(), $phpt, 1));

        $this->assertSame([], $collector->testMethodsGroupedByClass());
    }

    public function test_Filtered_issue_events_for_a_non_test_method_are_ignored(): void
    {
        $collector = $this->collector();
        $phpt      = new Phpt('Foo.phpt');

        $collector->testTriggeredDeprecation(
            new DeprecationTriggered($this->telemetryInfo(), $phpt, 'message', 'file.php', 1, false, false, false, IssueTrigger::from(null, null), 'trace'),
        );
        $collector->testTriggeredNotice(
            new NoticeTriggered($this->telemetryInfo(), $phpt, 'message', 'file.php', 1, false, false),
        );
        $collector->testTriggeredWarning(
            new WarningTriggered($this->telemetryInfo(), $phpt, 'message', 'file.php', 1, false, false),
        );
        $collector->testTriggeredPhpDeprecation(
            new PhpDeprecationTriggered($this->telemetryInfo(), $phpt, 'message', 'file.php', 1, false, false, false, IssueTrigger::from(null, null)),
        );
        $collector->testTriggeredPhpNotice(
            new PhpNoticeTriggered($this->telemetryInfo(), $phpt, 'message', 'file.php', 1, false, false),
        );
        $collector->testTriggeredPhpWarning(
            new PhpWarningTriggered($this->telemetryInfo(), $phpt, 'message', 'file.php', 1, false, false),
        );

        $this->assertSame([], $collector->testMethodsGroupedByClass());
    }

    private function collector(): TestResultCollector
    {
        return new TestResultCollector(new Facade, new IssueFilter($this->source()));
    }

    private function testMethod(string $methodName, string $className = TestDoxTest::class): TestMethod
    {
        return new TestMethod(
            $className,
            $methodName,
            $className . '.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName($className, $methodName),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }

    private function throwable(string $message): Throwable
    {
        return new Throwable('RuntimeException', $message, 'RuntimeException: ' . $message, '', null);
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
