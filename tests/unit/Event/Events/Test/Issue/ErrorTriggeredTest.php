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

use const PHP_EOL;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(ErrorTriggered::class)]
#[Small]
final class ErrorTriggeredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();
        $message       = 'message';
        $file          = 'file';
        $line          = 1;
        $suppressed    = false;

        $event = new ErrorTriggered(
            $telemetryInfo,
            $test,
            $message,
            $file,
            $line,
            $suppressed,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame('Test Triggered Error (FooTest::testBar) in file:1' . PHP_EOL . 'message', $event->asString());
    }

    public function testCanBeSuppressed(): void
    {
        $event = new ErrorTriggered(
            $this->telemetryInfo(),
            $this->testValueObject(),
            'message',
            'file',
            1,
            true,
        );

        $this->assertTrue($event->wasSuppressed());
        $this->assertSame('Test Triggered Error (FooTest::testBar, suppressed using operator) in file:1' . PHP_EOL . 'message', $event->asString());
    }
}
