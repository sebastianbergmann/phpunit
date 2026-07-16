<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Default\ProgressPrinter;

use function hrtime;
use Closure;
use PHPUnit\Event\Code\IssueTrigger\Code as IssueCode;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestRunner\ChildProcessErrored;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestSuite\TestSuiteWithName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;
use PHPUnit\TextUI\Output\Printer;

#[CoversClass(ProgressPrinter::class)]
#[Small]
final class ProgressPrinterTest extends TestCase
{
    public function testPrintsDotForSuccessfulTest(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
        $this->assertStringContainsString('1 / 1 (100%)', $buffer());
    }

    public function testPrintsErrorBeforeTestClassMethodErrored(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->beforeTestClassMethodErrored();
        $progress->testFinished();

        $this->assertStringStartsWith('E', $buffer());
    }

    public function testPrintsSkippedWhenSkippedBeforePrepare(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testSkipped();
        $progress->testFinished();

        $this->assertStringStartsWith('S', $buffer());
    }

    public function testPrintsSkippedAfterPrepare(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testSkipped();
        $progress->testFinished();

        $this->assertStringStartsWith('S', $buffer());
    }

    public function testTestSuiteSkippedSkipsEachTest(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(2));
        $progress->testSuiteSkipped(2);

        $this->assertStringStartsWith('SS', $buffer());
    }

    public function testPrintsIncompleteForMarkedIncomplete(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testMarkedIncomplete();
        $progress->testFinished();

        $this->assertStringStartsWith('I', $buffer());
    }

    public function testPrintsRiskyForRiskyTest(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testConsideredRisky();
        $progress->testFinished();

        $this->assertStringStartsWith('R', $buffer());
    }

    public function testPrintsFailureForFailedTest(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testFailed();
        $progress->testFinished();

        $this->assertStringStartsWith('F', $buffer());
    }

    public function testPrintsErrorWhenTestErroredAfterPrepare(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testErrored($this->erroredEvent());
        $progress->testFinished();

        $this->assertStringStartsWith('E', $buffer());
    }

    public function testPrintsErrorWhenTestErroredBeforePrepare(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testErrored($this->erroredEvent());
        $progress->testFinished();

        $this->assertStringStartsWith('E', $buffer());
    }

    public function testPrintsErrorWhenChildProcessErrored(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->childProcessErrored($this->childProcessErroredEvent());
        $progress->testErrored($this->erroredEvent());
        $progress->testFinished();

        $this->assertStringStartsWith('E', $buffer());
    }

    public function testPrintsNoticeForTriggeredNotice(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredNotice($this->noticeEvent(false, false));
        $progress->testFinished();

        $this->assertStringStartsWith('N', $buffer());
    }

    public function testTriggeredNoticeIsIgnoredWhenIgnoredByBaseline(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredNotice($this->noticeEvent(false, true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredNoticeIsIgnoredWhenSuppressed(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredNotice($this->noticeEvent(true, false));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredNoticeIsIgnoredWhenRestrictedByFile(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, restrictNotices: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredNotice($this->noticeEvent(false, false));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testPrintsNoticeForTriggeredPhpNotice(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpNotice($this->phpNoticeEvent(false, false));
        $progress->testFinished();

        $this->assertStringStartsWith('N', $buffer());
    }

    public function testTriggeredPhpNoticeIsIgnoredWhenIgnoredByBaseline(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpNotice($this->phpNoticeEvent(false, true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpNoticeIsIgnoredWhenSuppressed(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpNotice($this->phpNoticeEvent(true, false));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpNoticeIsIgnoredWhenRestrictedByFile(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, restrictNotices: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpNotice($this->phpNoticeEvent(false, false));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testPrintsDeprecationForTriggeredDeprecation(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredDeprecation($this->deprecationEvent());
        $progress->testFinished();

        $this->assertStringStartsWith('D', $buffer());
    }

    public function testTriggeredDeprecationIsIgnoredWhenIgnoredByBaseline(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredDeprecation($this->deprecationEvent(ignoredByBaseline: true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredDeprecationIsIgnoredWhenIgnoredByTest(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredDeprecation($this->deprecationEvent(ignoredByTest: true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredDeprecationIsIgnoredWhenIgnoredByFilter(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredDeprecation($this->deprecationEvent(ignoredByFilter: true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredDeprecationIsIgnoredWhenSelfAndIgnoreSelf(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, ignoreSelfDeprecations: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredDeprecation($this->deprecationEvent(trigger: IssueTrigger::from(IssueCode::FirstParty, IssueCode::ThirdParty)));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredDeprecationIsIgnoredWhenDirectAndIgnoreDirect(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, ignoreDirectDeprecations: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredDeprecation($this->deprecationEvent(trigger: IssueTrigger::from(IssueCode::ThirdParty, IssueCode::FirstParty)));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredDeprecationIsIgnoredWhenIndirectAndIgnoreIndirect(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, ignoreIndirectDeprecations: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredDeprecation($this->deprecationEvent(trigger: IssueTrigger::from(IssueCode::ThirdParty, IssueCode::ThirdParty)));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredDeprecationIsIgnoredWhenSuppressed(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredDeprecation($this->deprecationEvent(suppressed: true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testPrintsDeprecationForTriggeredPhpDeprecation(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpDeprecation($this->phpDeprecationEvent());
        $progress->testFinished();

        $this->assertStringStartsWith('D', $buffer());
    }

    public function testTriggeredPhpDeprecationIsIgnoredWhenIgnoredByBaseline(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpDeprecation($this->phpDeprecationEvent(ignoredByBaseline: true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpDeprecationIsIgnoredWhenIgnoredByTest(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpDeprecation($this->phpDeprecationEvent(ignoredByTest: true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpDeprecationIsIgnoredWhenIgnoredByFilter(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpDeprecation($this->phpDeprecationEvent(ignoredByFilter: true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpDeprecationIsIgnoredWhenSelfAndIgnoreSelf(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, ignoreSelfDeprecations: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpDeprecation($this->phpDeprecationEvent(trigger: IssueTrigger::from(IssueCode::FirstParty, IssueCode::ThirdParty)));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpDeprecationIsIgnoredWhenDirectAndIgnoreDirect(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, ignoreDirectDeprecations: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpDeprecation($this->phpDeprecationEvent(trigger: IssueTrigger::from(IssueCode::ThirdParty, IssueCode::FirstParty)));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpDeprecationIsIgnoredWhenIndirectAndIgnoreIndirect(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, ignoreIndirectDeprecations: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpDeprecation($this->phpDeprecationEvent(trigger: IssueTrigger::from(IssueCode::ThirdParty, IssueCode::ThirdParty)));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpDeprecationIsIgnoredWhenSuppressed(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpDeprecation($this->phpDeprecationEvent(suppressed: true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testPrintsDeprecationForTriggeredPhpunitDeprecation(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpunitDeprecation();
        $progress->testFinished();

        $this->assertStringStartsWith('D', $buffer());
    }

    public function testPrintsNoticeForTriggeredPhpunitNotice(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpunitNotice();
        $progress->testFinished();

        $this->assertStringStartsWith('N', $buffer());
    }

    public function testPrintsWarningForTriggeredWarning(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredWarning($this->warningEvent(false, false));
        $progress->testFinished();

        $this->assertStringStartsWith('W', $buffer());
    }

    public function testTriggeredWarningIsIgnoredWhenIgnoredByBaseline(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredWarning($this->warningEvent(false, true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredWarningIsIgnoredWhenSuppressed(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredWarning($this->warningEvent(true, false));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredWarningIsIgnoredWhenRestrictedByFile(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, restrictWarnings: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredWarning($this->warningEvent(false, false));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testPrintsWarningForTriggeredPhpWarning(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpWarning($this->phpWarningEvent(false, false));
        $progress->testFinished();

        $this->assertStringStartsWith('W', $buffer());
    }

    public function testTriggeredPhpWarningIsIgnoredWhenIgnoredByBaseline(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpWarning($this->phpWarningEvent(false, true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpWarningIsIgnoredWhenSuppressed(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpWarning($this->phpWarningEvent(true, false));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testTriggeredPhpWarningIsIgnoredWhenRestrictedByFile(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, restrictWarnings: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpWarning($this->phpWarningEvent(false, false));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testPrintsWarningForTriggeredPhpunitWarning(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpunitWarning($this->phpunitWarningEvent(false));
        $progress->testFinished();

        $this->assertStringStartsWith('W', $buffer());
    }

    public function testTriggeredPhpunitWarningIsIgnoredWhenIgnoredByTest(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredPhpunitWarning($this->phpunitWarningEvent(true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testPrintsErrorForTriggeredError(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredError($this->errorTriggeredEvent(false));
        $progress->testFinished();

        $this->assertStringStartsWith('E', $buffer());
    }

    public function testTriggeredErrorIsIgnoredWhenSuppressed(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testTriggeredError($this->errorTriggeredEvent(true));
        $progress->testFinished();

        $this->assertStringStartsWith('.', $buffer());
    }

    public function testMoreImportantStatusIsNotOverwrittenByLessImportantOne(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testFailed();
        $progress->testTriggeredNotice($this->noticeEvent(false, false));
        $progress->testFinished();

        $this->assertStringStartsWith('F', $buffer());
    }

    public function testColorsArePrintedWhenEnabled(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, colors: true);

        $progress->testRunnerExecutionStarted($this->executionStarted(1));
        $progress->testPrepared();
        $progress->testFailed();
        $progress->testFinished();

        $this->assertStringContainsString("\x1b[", $buffer());
    }

    public function testRowIsFlushedWhenColumnReachesMax(): void
    {
        [$printer, $buffer] = $this->printer();
        $progress           = $this->progressPrinter($printer, numberOfTests: 200);

        $progress->testRunnerExecutionStarted($this->executionStarted(200));

        for ($i = 0; $i < 70; $i++) {
            $progress->testPrepared();
            $progress->testFinished();
        }

        $this->assertStringContainsString("\n", $buffer());
    }

    private function progressPrinter(
        Printer $printer,
        bool $colors = false,
        int $numberOfTests = 1,
        bool $restrictNotices = false,
        bool $restrictWarnings = false,
        bool $ignoreSelfDeprecations = false,
        bool $ignoreDirectDeprecations = false,
        bool $ignoreIndirectDeprecations = false,
    ): ProgressPrinter {
        return new ProgressPrinter(
            $printer,
            new EventFacade,
            $colors,
            80,
            $this->source(
                restrictNotices: $restrictNotices,
                restrictWarnings: $restrictWarnings,
                ignoreSelfDeprecations: $ignoreSelfDeprecations,
                ignoreDirectDeprecations: $ignoreDirectDeprecations,
                ignoreIndirectDeprecations: $ignoreIndirectDeprecations,
            ),
        );
    }

    /**
     * @return array{0: Printer, 1: Closure(): string}
     */
    private function printer(): array
    {
        $buffer  = '';
        $printer = $this->createMock(Printer::class);
        $printer->method('print')->willReturnCallback(
            static function (string $s) use (&$buffer): void
            {
                $buffer .= $s;
            },
        );

        return [
            $printer,
            static function () use (&$buffer): string
            {
                return $buffer;
            },
        ];
    }

    private function source(
        bool $restrictNotices = false,
        bool $restrictWarnings = false,
        bool $ignoreSelfDeprecations = false,
        bool $ignoreDirectDeprecations = false,
        bool $ignoreIndirectDeprecations = false,
    ): Source {
        return new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            $restrictNotices,
            $restrictWarnings,
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
            $ignoreSelfDeprecations,
            $ignoreDirectDeprecations,
            $ignoreIndirectDeprecations,
            true,
        );
    }

    private function executionStarted(int $numberOfTests): ExecutionStarted
    {
        return new ExecutionStarted(
            $this->telemetryInfo(),
            new TestSuiteWithName('foo', $numberOfTests, TestCollection::fromArray([])),
        );
    }

    private function erroredEvent(): Errored
    {
        return new Errored(
            $this->telemetryInfo(),
            $this->testMethod(),
            ThrowableBuilder::from(new Exception('message')),
        );
    }

    private function childProcessErroredEvent(): ChildProcessErrored
    {
        return new ChildProcessErrored(
            $this->telemetryInfo(),
            ChildProcessReason::TestRequiringProcessIsolation,
            'message',
        );
    }

    private function noticeEvent(bool $suppressed, bool $ignoredByBaseline): NoticeTriggered
    {
        return new NoticeTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'Foo.php',
            1,
            $suppressed,
            $ignoredByBaseline,
        );
    }

    private function phpNoticeEvent(bool $suppressed, bool $ignoredByBaseline): PhpNoticeTriggered
    {
        return new PhpNoticeTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'Foo.php',
            1,
            $suppressed,
            $ignoredByBaseline,
        );
    }

    private function deprecationEvent(
        bool $suppressed = false,
        bool $ignoredByBaseline = false,
        bool $ignoredByTest = false,
        bool $ignoredByFilter = false,
        ?IssueTrigger $trigger = null,
    ): DeprecationTriggered {
        return new DeprecationTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'Foo.php',
            1,
            $suppressed,
            $ignoredByBaseline,
            $ignoredByTest,
            $ignoredByFilter,
            $trigger ?? IssueTrigger::from(null, null),
            'stack trace',
        );
    }

    private function phpDeprecationEvent(
        bool $suppressed = false,
        bool $ignoredByBaseline = false,
        bool $ignoredByTest = false,
        bool $ignoredByFilter = false,
        ?IssueTrigger $trigger = null,
    ): PhpDeprecationTriggered {
        return new PhpDeprecationTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'Foo.php',
            1,
            $suppressed,
            $ignoredByBaseline,
            $ignoredByTest,
            $ignoredByFilter,
            $trigger ?? IssueTrigger::from(null, null),
        );
    }

    private function warningEvent(bool $suppressed, bool $ignoredByBaseline): WarningTriggered
    {
        return new WarningTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'Foo.php',
            1,
            $suppressed,
            $ignoredByBaseline,
        );
    }

    private function phpWarningEvent(bool $suppressed, bool $ignoredByBaseline): PhpWarningTriggered
    {
        return new PhpWarningTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'Foo.php',
            1,
            $suppressed,
            $ignoredByBaseline,
        );
    }

    private function phpunitWarningEvent(bool $ignoredByTest): PhpunitWarningTriggered
    {
        return new PhpunitWarningTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            $ignoredByTest,
        );
    }

    private function errorTriggeredEvent(bool $suppressed): ErrorTriggered
    {
        return new ErrorTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'Foo.php',
            1,
            $suppressed,
        );
    }

    private function testMethod(): TestMethod
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
