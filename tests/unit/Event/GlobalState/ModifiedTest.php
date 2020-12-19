<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\GlobalState;

use PHPUnit\Event\AbstractEventTestCase;
use SebastianBergmann\GlobalState\Snapshot;

/**
 * @covers \PHPUnit\Event\GlobalState\Modified
 */
final class ModifiedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo  = self::createTelemetryInfo();
        $snapshotBefore = new Snapshot();
        $snapshotAfter  = new Snapshot();
        $message        = 'Hmm, who would have thought?';

        $event = new Modified(
            $telemetryInfo,
            $snapshotBefore,
            $snapshotAfter,
            $message
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($snapshotBefore, $event->snapshotBefore());
        $this->assertSame($snapshotAfter, $event->snapshotAfter());
        $this->assertSame($message, $event->message());
    }
}
