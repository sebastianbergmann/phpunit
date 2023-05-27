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
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Finished::class)]
#[Small]
final class FinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();

        $event = new Finished(
            $telemetryInfo,
            $test,
            1,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame(1, $event->numberOfAssertionsPerformed());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Finished(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1,
        );

        $this->assertSame('Test Finished (FooTest::testBar)', $event->asString());
    }
}
