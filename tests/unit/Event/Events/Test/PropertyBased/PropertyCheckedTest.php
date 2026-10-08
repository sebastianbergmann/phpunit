<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(PropertyChecked::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class PropertyCheckedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();

        $event = new PropertyChecked($telemetryInfo, $test, 1234, 100, 3);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame(1234, $event->seed());
        $this->assertSame(100, $event->numberOfExamplesChecked());
        $this->assertSame(3, $event->numberOfExamplesRejected());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new PropertyChecked($this->telemetryInfo(), $this->testValueObject(), 1234, 100, 3);

        $this->assertSame('Test Property Checked (FooTest::testBar, seed: 1234, examples: 100, rejected: 3)', $event->asString());
    }
}
