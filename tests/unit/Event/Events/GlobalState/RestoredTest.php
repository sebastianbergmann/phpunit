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
use PHPUnit\Framework\Attributes\CoversClass;
use SebastianBergmann\GlobalState\Snapshot;

#[CoversClass(Restored::class)]
final class RestoredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $snapshot      = new Snapshot;

        $event = new Restored(
            $telemetryInfo,
            $snapshot
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($snapshot, $event->snapshot());
    }
}
