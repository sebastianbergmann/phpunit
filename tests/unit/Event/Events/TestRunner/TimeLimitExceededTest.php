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

#[CoversClass(TimeLimitExceeded::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class TimeLimitExceededTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();

        $event = new TimeLimitExceeded($telemetryInfo, 60);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame(60, $event->timeLimit());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new TimeLimitExceeded($this->telemetryInfo(), 60);

        $this->assertSame('Test Runner Time Limit Exceeded (60 seconds)', $event->asString());
    }

    public function testCanBeRepresentedAsStringForTimeLimitOfOneSecond(): void
    {
        $event = new TimeLimitExceeded($this->telemetryInfo(), 1);

        $this->assertSame('Test Runner Time Limit Exceeded (1 second)', $event->asString());
    }
}
