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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(ChildProcessErrored::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class ChildProcessErroredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $reason        = ChildProcessReason::ParallelWorker;
        $message       = 'message';

        $event = new ChildProcessErrored(
            $telemetryInfo,
            $reason,
            $message,
            0,
            null,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($reason, $event->reason());
        $this->assertSame($message, $event->message());
        $this->assertSame(0, $event->exitCode());
        $this->assertNull($event->signal());
    }

    public function testMayHaveSignalInsteadOfExitCode(): void
    {
        $event = new ChildProcessErrored(
            $this->telemetryInfo(),
            ChildProcessReason::TestRequiringProcessIsolation,
            'message',
            null,
            9,
        );

        $this->assertNull($event->exitCode());
        $this->assertSame(9, $event->signal());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new ChildProcessErrored(
            $this->telemetryInfo(),
            ChildProcessReason::ParallelWorker,
            'message',
            0,
            null,
        );

        $this->assertSame('Child Process Errored (worker for parallel test execution)', $event->asString());
    }

    public function testCanBeRepresentedAsStringWhenItIsNotKnownHowChildProcessEnded(): void
    {
        $event = new ChildProcessErrored(
            $this->telemetryInfo(),
            ChildProcessReason::TestRequiringProcessIsolation,
            'message',
            null,
            null,
        );

        $this->assertSame('Child Process Errored (test requiring process isolation)', $event->asString());
    }

    public function testCanBeRepresentedAsStringWhenChildProcessEndedWithExitCodeOtherThanZero(): void
    {
        $event = new ChildProcessErrored(
            $this->telemetryInfo(),
            ChildProcessReason::TestRequiringProcessIsolation,
            'message',
            2,
            null,
        );

        $this->assertSame('Child Process Errored (test requiring process isolation, exit code 2)', $event->asString());
    }

    public function testCanBeRepresentedAsStringWhenChildProcessWasTerminatedBySignal(): void
    {
        $event = new ChildProcessErrored(
            $this->telemetryInfo(),
            ChildProcessReason::TestRequiringProcessIsolation,
            'message',
            null,
            9,
        );

        $this->assertSame('Child Process Errored (test requiring process isolation, terminated by signal 9)', $event->asString());
    }
}
