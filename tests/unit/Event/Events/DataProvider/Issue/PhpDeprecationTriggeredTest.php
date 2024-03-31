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
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(PhpDeprecationTriggered::class)]
#[Small]
final class PhpDeprecationTriggeredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo     = $this->telemetryInfo();
        $dataProvider      = new ClassMethod('the-class', 'the-method');
        $message           = 'message';
        $file              = 'file';
        $line              = 1;
        $suppressed        = false;
        $ignoredByBaseline = false;
        $trigger           = IssueTrigger::unknown();

        $event = new PhpDeprecationTriggered(
            $telemetryInfo,
            $dataProvider,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
            $trigger,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($dataProvider, $event->dataProvider());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame($ignoredByBaseline, $event->ignoredByBaseline());
        $this->assertSame('Data Provider Triggered PHP Deprecation (the-class::the-method, unknown if issue was triggered in first-party code or third-party code)' . PHP_EOL . 'message', $event->asString());
        $this->assertSame($trigger, $event->trigger());
    }

    public function testCanBeIgnoredByBaseline(): void
    {
        $event = new PhpDeprecationTriggered(
            $this->telemetryInfo(),
            new ClassMethod('the-class', 'the-method'),
            'message',
            'file',
            1,
            false,
            true,
            IssueTrigger::unknown(),
        );

        $this->assertTrue($event->ignoredByBaseline());
        $this->assertSame('Data Provider Triggered PHP Deprecation (the-class::the-method, unknown if issue was triggered in first-party code or third-party code, ignored by baseline)' . PHP_EOL . 'message', $event->asString());
    }

    public function testCanBeSuppressed(): void
    {
        $event = new PhpDeprecationTriggered(
            $this->telemetryInfo(),
            new ClassMethod('the-class', 'the-method'),
            'message',
            'file',
            1,
            true,
            false,
            IssueTrigger::unknown(),
        );

        $this->assertTrue($event->wasSuppressed());
        $this->assertSame('Data Provider Triggered PHP Deprecation (the-class::the-method, unknown if issue was triggered in first-party code or third-party code, suppressed using operator)' . PHP_EOL . 'message', $event->asString());
    }
}
