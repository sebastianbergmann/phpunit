<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\DataProvider;

use const PHP_EOL;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(ErrorTriggered::class)]
#[Small]
final class ErrorTriggeredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $dataProvider  = new ClassMethod('the-class', 'the-method');
        $message       = 'message';
        $file          = 'file';
        $line          = 1;
        $suppressed    = false;

        $event = new ErrorTriggered(
            $telemetryInfo,
            $dataProvider,
            $message,
            $file,
            $line,
            $suppressed,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($dataProvider, $event->dataProvider());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame('Data Provider Triggered Error (the-class::the-method)' . PHP_EOL . 'message', $event->asString());
    }

    public function testCanBeSuppressed(): void
    {
        $event = new ErrorTriggered(
            $this->telemetryInfo(),
            new ClassMethod('the-class', 'the-method'),
            'message',
            'file',
            1,
            true,
        );

        $this->assertTrue($event->wasSuppressed());
        $this->assertSame('Data Provider Triggered Error (the-class::the-method, suppressed using operator)' . PHP_EOL . 'message', $event->asString());
    }
}
