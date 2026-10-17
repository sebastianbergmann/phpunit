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
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($reason, $event->reason());
        $this->assertSame($message, $event->message());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new ChildProcessErrored(
            $this->telemetryInfo(),
            ChildProcessReason::ParallelWorker,
            'message',
        );

        $this->assertSame('Child Process Errored (worker for parallel test execution)', $event->asString());
    }
}
