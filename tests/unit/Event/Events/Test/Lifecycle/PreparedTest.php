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

#[CoversClass(Prepared::class)]
#[Small]
final class PreparedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();

        $event = new Prepared(
            $telemetryInfo,
            $test,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Prepared(
            $this->telemetryInfo(),
            $this->testValueObject(),
        );

        $this->assertSame('Test Prepared (FooTest::testBar)', $event->asString());
    }
}
