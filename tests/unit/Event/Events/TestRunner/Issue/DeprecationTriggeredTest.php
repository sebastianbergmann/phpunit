<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner\Issue;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(DeprecationTriggered::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class DeprecationTriggeredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo     = $this->telemetryInfo();
        $message           = 'message';
        $file              = 'file';
        $line              = 1;
        $suppressed        = false;
        $ignoredByBaseline = false;
        $trigger           = IssueTrigger::from(null, null);
        $stackTrace        = 'stack trace';

        $event = new DeprecationTriggered(
            $telemetryInfo,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
            $trigger,
            $stackTrace,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertFalse($event->wasSuppressed());
        $this->assertFalse($event->ignoredByBaseline());
        $this->assertSame($trigger, $event->trigger());
        $this->assertSame($stackTrace, $event->stackTrace());
        $this->assertStringContainsString('Test Runner Triggered Deprecation (', $event->asString());
        $this->assertStringContainsString('in file:1', $event->asString());
    }

    public function testCanBeIgnoredByBaseline(): void
    {
        $event = new DeprecationTriggered(
            $this->telemetryInfo(),
            'message',
            'file',
            1,
            false,
            true,
            IssueTrigger::from(null, null),
            'stack trace',
        );

        $this->assertTrue($event->ignoredByBaseline());
        $this->assertStringContainsString('ignored by baseline', $event->asString());
    }

    public function testCanBeSuppressed(): void
    {
        $event = new DeprecationTriggered(
            $this->telemetryInfo(),
            'message',
            'file',
            1,
            true,
            false,
            IssueTrigger::from(null, null),
            'stack trace',
        );

        $this->assertTrue($event->wasSuppressed());
        $this->assertStringContainsString('suppressed using operator', $event->asString());
    }
}
