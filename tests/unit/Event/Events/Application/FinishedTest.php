<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Application;

use DateTimeImmutable;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Runner\Receipt\IssueCounts;
use PHPUnit\Runner\Receipt\Outcome;
use PHPUnit\Runner\Receipt\Status;

#[CoversClass(Finished::class)]
#[UsesClass(IssueCounts::class)]
#[UsesClass(Outcome::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class FinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $outcome       = $this->outcome(1);

        $event = new Finished($telemetryInfo, $outcome);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($outcome, $event->outcome());
    }

    public function testHasShellExitCodeOfOutcome(): void
    {
        $event = new Finished($this->telemetryInfo(), $this->outcome(1));

        $this->assertSame(1, $event->shellExitCode());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Finished($this->telemetryInfo(), $this->outcome(0));

        $this->assertSame('PHPUnit Finished (Shell Exit Code: 0)', $event->asString());
    }

    private function outcome(int $shellExitCode): Outcome
    {
        return new Outcome(
            new DateTimeImmutable,
            Status::Completed,
            $shellExitCode,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0),
            new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0),
        );
    }
}
