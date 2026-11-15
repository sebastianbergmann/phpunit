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

#[CoversClass(ChildProcessFinished::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class ChildProcessFinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $reason        = ChildProcessReason::PhptTest;
        $stdout        = 'output';
        $stderr        = 'error';

        $event = new ChildProcessFinished(
            $telemetryInfo,
            $reason,
            $stdout,
            $stderr,
            0,
            null,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($reason, $event->reason());
        $this->assertSame($stdout, $event->stdout());
        $this->assertSame($stderr, $event->stderr());
        $this->assertSame(0, $event->exitCode());
        $this->assertNull($event->signal());
    }

    public function testMayHaveSignalInsteadOfExitCode(): void
    {
        $event = new ChildProcessFinished(
            $this->telemetryInfo(),
            ChildProcessReason::PhptTest,
            'output',
            'error',
            null,
            9,
        );

        $this->assertNull($event->exitCode());
        $this->assertSame(9, $event->signal());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new ChildProcessFinished(
            $this->telemetryInfo(),
            ChildProcessReason::PhptTest,
            'output',
            'error',
            0,
            null,
        );

        $this->assertSame('Child Process Finished (FILE section of a PHPT test)', $event->asString());
    }

    public function testCanBeRepresentedAsStringWhenItIsNotKnownHowChildProcessEnded(): void
    {
        $event = new ChildProcessFinished(
            $this->telemetryInfo(),
            ChildProcessReason::PhptTest,
            'output',
            'error',
            null,
            null,
        );

        $this->assertSame('Child Process Finished (FILE section of a PHPT test)', $event->asString());
    }

    public function testCanBeRepresentedAsStringWhenChildProcessEndedWithExitCodeOtherThanZero(): void
    {
        $event = new ChildProcessFinished(
            $this->telemetryInfo(),
            ChildProcessReason::PhptTest,
            'output',
            'error',
            2,
            null,
        );

        $this->assertSame('Child Process Finished (FILE section of a PHPT test, exit code 2)', $event->asString());
    }

    public function testCanBeRepresentedAsStringWhenChildProcessWasTerminatedBySignal(): void
    {
        $event = new ChildProcessFinished(
            $this->telemetryInfo(),
            ChildProcessReason::PhptTest,
            'output',
            'error',
            null,
            9,
        );

        $this->assertSame('Child Process Finished (FILE section of a PHPT test, terminated by signal 9)', $event->asString());
    }
}
