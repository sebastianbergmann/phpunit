<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Sorted::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class SortedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $pipeline      = ['duration-ascending', 'defects', 'resolve-dependencies'];

        $event = new Sorted(
            $telemetryInfo,
            $pipeline,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($pipeline, $event->pipeline());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Sorted(
            $this->telemetryInfo(),
            ['duration-ascending', 'defects', 'resolve-dependencies'],
        );

        $this->assertSame('Test Suite Sorted', $event->asString());
    }
}
