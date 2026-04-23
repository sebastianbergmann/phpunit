<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner;

use function hrtime;
use PHPUnit\Event\Code\IssueTrigger\Code;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(IssueFilter::class)]
#[Small]
#[Group('test-runner')]
final class IssueFilterTest extends TestCase
{
    public function testProcessesDeprecationByDefault(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertTrue(
            $filter->shouldBeProcessed($this->deprecationEvent()),
        );
    }

    public function testDoesNotProcessEventFromNonTestMethodWhenOnlyTestMethodsIsTrue(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertFalse(
            $filter->shouldBeProcessed($this->deprecationEventWithPhptTest(), true),
        );
    }

    public function testProcessesEventFromTestMethodWhenOnlyTestMethodsIsTrue(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertTrue(
            $filter->shouldBeProcessed($this->deprecationEvent(), true),
        );
    }

    public function testDoesNotProcessDeprecationIgnoredByTest(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertFalse(
            $filter->shouldBeProcessed($this->deprecationEvent(ignoredByTest: true)),
        );
    }

    public function testDoesNotProcessPhpDeprecationIgnoredByTest(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertFalse(
            $filter->shouldBeProcessed($this->phpDeprecationEvent(ignoredByTest: true)),
        );
    }

    public function testDoesNotProcessSelfDeprecationWhenIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSelfDeprecations: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->deprecationEvent(trigger: IssueTrigger::from(Code::FirstParty, null))),
        );
    }

    public function testProcessesSelfDeprecationWhenNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSelfDeprecations: false));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->deprecationEvent(trigger: IssueTrigger::from(Code::FirstParty, null))),
        );
    }

    public function testDoesNotProcessDirectDeprecationWhenIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreDirectDeprecations: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->deprecationEvent(trigger: IssueTrigger::from(Code::ThirdParty, Code::FirstParty))),
        );
    }

    public function testProcessesDirectDeprecationWhenNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreDirectDeprecations: false));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->deprecationEvent(trigger: IssueTrigger::from(Code::ThirdParty, Code::FirstParty))),
        );
    }

    public function testDoesNotProcessIndirectDeprecationWhenIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreIndirectDeprecations: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->deprecationEvent(trigger: IssueTrigger::from(Code::ThirdParty, Code::ThirdParty))),
        );
    }

    public function testProcessesIndirectDeprecationWhenNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreIndirectDeprecations: false));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->deprecationEvent(trigger: IssueTrigger::from(Code::ThirdParty, Code::ThirdParty))),
        );
    }

    public function testDoesNotProcessSuppressedDeprecationWhenSuppressionNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfDeprecations: false));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->deprecationEvent(suppressed: true)),
        );
    }

    public function testProcessesSuppressedDeprecationWhenSuppressionIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfDeprecations: true));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->deprecationEvent(suppressed: true)),
        );
    }

    public function testDoesNotProcessSuppressedPhpDeprecationWhenSuppressionNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfDeprecations: false));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->phpDeprecationEvent(suppressed: true)),
        );
    }

    public function testProcessesSuppressedPhpDeprecationWhenSuppressionIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfDeprecations: true));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->phpDeprecationEvent(suppressed: true)),
        );
    }

    public function testDoesNotProcessPhpDeprecationWithSelfTriggerWhenIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSelfDeprecations: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->phpDeprecationEvent(trigger: IssueTrigger::from(Code::FirstParty, null))),
        );
    }

    public function testDoesNotProcessPhpDeprecationWithDirectTriggerWhenIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreDirectDeprecations: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->phpDeprecationEvent(trigger: IssueTrigger::from(Code::ThirdParty, Code::FirstParty))),
        );
    }

    public function testDoesNotProcessPhpDeprecationWithIndirectTriggerWhenIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreIndirectDeprecations: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->phpDeprecationEvent(trigger: IssueTrigger::from(Code::ThirdParty, Code::ThirdParty))),
        );
    }

    public function testProcessesNoticeByDefault(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertTrue(
            $filter->shouldBeProcessed($this->noticeEvent()),
        );
    }

    public function testDoesNotProcessSuppressedNoticeWhenSuppressionNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfNotices: false));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->noticeEvent(suppressed: true)),
        );
    }

    public function testProcessesSuppressedNoticeWhenSuppressionIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfNotices: true));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->noticeEvent(suppressed: true)),
        );
    }

    public function testDoesNotProcessNoticeFromFileNotInSourceWhenRestrictNoticesIsEnabled(): void
    {
        $filter = new IssueFilter($this->source(restrictNotices: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->noticeEvent()),
        );
    }

    public function testProcessesNoticeWhenRestrictNoticesIsDisabled(): void
    {
        $filter = new IssueFilter($this->source(restrictNotices: false));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->noticeEvent()),
        );
    }

    public function testProcessesPhpNoticeByDefault(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertTrue(
            $filter->shouldBeProcessed($this->phpNoticeEvent()),
        );
    }

    public function testDoesNotProcessSuppressedPhpNoticeWhenSuppressionNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfPhpNotices: false));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->phpNoticeEvent(suppressed: true)),
        );
    }

    public function testProcessesSuppressedPhpNoticeWhenSuppressionIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfPhpNotices: true));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->phpNoticeEvent(suppressed: true)),
        );
    }

    public function testDoesNotProcessPhpNoticeFromFileNotInSourceWhenRestrictNoticesIsEnabled(): void
    {
        $filter = new IssueFilter($this->source(restrictNotices: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->phpNoticeEvent()),
        );
    }

    public function testProcessesPhpNoticeWhenRestrictNoticesIsDisabled(): void
    {
        $filter = new IssueFilter($this->source(restrictNotices: false));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->phpNoticeEvent()),
        );
    }

    public function testProcessesWarningByDefault(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertTrue(
            $filter->shouldBeProcessed($this->warningEvent()),
        );
    }

    public function testDoesNotProcessSuppressedWarningWhenSuppressionNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfWarnings: false));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->warningEvent(suppressed: true)),
        );
    }

    public function testProcessesSuppressedWarningWhenSuppressionIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfWarnings: true));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->warningEvent(suppressed: true)),
        );
    }

    public function testDoesNotProcessWarningFromFileNotInSourceWhenRestrictWarningsIsEnabled(): void
    {
        $filter = new IssueFilter($this->source(restrictWarnings: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->warningEvent()),
        );
    }

    public function testProcessesWarningWhenRestrictWarningsIsDisabled(): void
    {
        $filter = new IssueFilter($this->source(restrictWarnings: false));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->warningEvent()),
        );
    }

    public function testProcessesPhpWarningByDefault(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertTrue(
            $filter->shouldBeProcessed($this->phpWarningEvent()),
        );
    }

    public function testDoesNotProcessSuppressedPhpWarningWhenSuppressionNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfPhpWarnings: false));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->phpWarningEvent(suppressed: true)),
        );
    }

    public function testProcessesSuppressedPhpWarningWhenSuppressionIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfPhpWarnings: true));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->phpWarningEvent(suppressed: true)),
        );
    }

    public function testDoesNotProcessPhpWarningFromFileNotInSourceWhenRestrictWarningsIsEnabled(): void
    {
        $filter = new IssueFilter($this->source(restrictWarnings: true));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->phpWarningEvent()),
        );
    }

    public function testProcessesPhpWarningWhenRestrictWarningsIsDisabled(): void
    {
        $filter = new IssueFilter($this->source(restrictWarnings: false));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->phpWarningEvent()),
        );
    }

    public function testProcessesErrorByDefault(): void
    {
        $filter = new IssueFilter($this->source());

        $this->assertTrue(
            $filter->shouldBeProcessed($this->errorEvent()),
        );
    }

    public function testDoesNotProcessSuppressedErrorWhenSuppressionNotIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfErrors: false));

        $this->assertFalse(
            $filter->shouldBeProcessed($this->errorEvent(suppressed: true)),
        );
    }

    public function testProcessesSuppressedErrorWhenSuppressionIgnored(): void
    {
        $filter = new IssueFilter($this->source(ignoreSuppressionOfErrors: true));

        $this->assertTrue(
            $filter->shouldBeProcessed($this->errorEvent(suppressed: true)),
        );
    }

    private function deprecationEvent(bool $suppressed = false, bool $ignoredByTest = false, ?IssueTrigger $trigger = null): DeprecationTriggered
    {
        return new DeprecationTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'file.php',
            1,
            $suppressed,
            false,
            $ignoredByTest,
            $trigger ?? IssueTrigger::from(null, null),
            'stack trace',
        );
    }

    private function deprecationEventWithPhptTest(bool $suppressed = false): DeprecationTriggered
    {
        return new DeprecationTriggered(
            $this->telemetryInfo(),
            new Phpt('test.phpt'),
            'message',
            'file.php',
            1,
            $suppressed,
            false,
            false,
            IssueTrigger::from(null, null),
            'stack trace',
        );
    }

    private function phpDeprecationEvent(bool $suppressed = false, bool $ignoredByTest = false, ?IssueTrigger $trigger = null): PhpDeprecationTriggered
    {
        return new PhpDeprecationTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'file.php',
            1,
            $suppressed,
            false,
            $ignoredByTest,
            $trigger ?? IssueTrigger::from(null, null),
        );
    }

    private function noticeEvent(bool $suppressed = false): NoticeTriggered
    {
        return new NoticeTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'file.php',
            1,
            $suppressed,
            false,
        );
    }

    private function phpNoticeEvent(bool $suppressed = false): PhpNoticeTriggered
    {
        return new PhpNoticeTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'file.php',
            1,
            $suppressed,
            false,
        );
    }

    private function warningEvent(bool $suppressed = false): WarningTriggered
    {
        return new WarningTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'file.php',
            1,
            $suppressed,
            false,
        );
    }

    private function phpWarningEvent(bool $suppressed = false): PhpWarningTriggered
    {
        return new PhpWarningTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'file.php',
            1,
            $suppressed,
            false,
        );
    }

    private function errorEvent(bool $suppressed = false): ErrorTriggered
    {
        return new ErrorTriggered(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message',
            'file.php',
            1,
            $suppressed,
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

    private function source(bool $ignoreSelfDeprecations = false, bool $ignoreDirectDeprecations = false, bool $ignoreIndirectDeprecations = false, bool $ignoreSuppressionOfDeprecations = false, bool $ignoreSuppressionOfErrors = false, bool $ignoreSuppressionOfNotices = false, bool $ignoreSuppressionOfPhpNotices = false, bool $ignoreSuppressionOfWarnings = false, bool $ignoreSuppressionOfPhpWarnings = false, bool $restrictNotices = false, bool $restrictWarnings = false): Source
    {
        return new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            $restrictNotices,
            $restrictWarnings,
            $ignoreSuppressionOfDeprecations,
            $ignoreSuppressionOfDeprecations,
            $ignoreSuppressionOfErrors,
            $ignoreSuppressionOfNotices,
            $ignoreSuppressionOfPhpNotices,
            $ignoreSuppressionOfWarnings,
            $ignoreSuppressionOfPhpWarnings,
            [
                'functions'               => [],
                'methods'                 => [],
                'ignoreUndefinedTriggers' => true,
            ],
            $ignoreSelfDeprecations,
            $ignoreDirectDeprecations,
            $ignoreIndirectDeprecations,
            false,
        );
    }
}
