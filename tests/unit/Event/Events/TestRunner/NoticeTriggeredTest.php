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
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(NoticeTriggered::class)]
#[Small]
final class NoticeTriggeredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $message       = 'message';

        $event = new NoticeTriggered(
            $telemetryInfo,
            $message,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($message, $event->message());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new NoticeTriggered(
            $this->telemetryInfo(),
            'message',
        );

        $this->assertSame('Test Runner Triggered Notice (message)', $event->asString());
    }
}
