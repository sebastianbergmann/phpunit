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
        $test              = $this->testValueObject();
        $message           = 'message';
        $file              = 'file';
        $line              = 1;
        $suppressed        = false;
        $ignoredByBaseline = false;
        $ignoredByTest     = false;
        $trigger           = IssueTrigger::unknown();

        $event = new PhpDeprecationTriggered(
            $telemetryInfo,
            $test,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
            $ignoredByTest,
            $trigger,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame($ignoredByBaseline, $event->ignoredByBaseline());
        $this->assertSame($ignoredByTest, $event->ignoredByTest());
        $this->assertSame('Test Triggered PHP Deprecation (FooTest::testBar, unknown if issue was triggered in first-party code or third-party code) in file:1' . PHP_EOL . 'message', $event->asString());
        $this->assertSame($trigger, $event->trigger());
    }

    public function testCanBeIgnoredByBaseline(): void
    {
        $event = new PhpDeprecationTriggered(
            $this->telemetryInfo(),
            $this->testValueObject(),
            'message',
            'file',
            1,
            false,
            true,
            false,
            IssueTrigger::unknown(),
        );

        $this->assertTrue($event->ignoredByBaseline());
        $this->assertSame('Test Triggered PHP Deprecation (FooTest::testBar, unknown if issue was triggered in first-party code or third-party code, ignored by baseline) in file:1' . PHP_EOL . 'message', $event->asString());
    }

    public function testCanBeIgnoredByTest(): void
    {
        $event = new PhpDeprecationTriggered(
            $this->telemetryInfo(),
            $this->testValueObject(),
            'message',
            'file',
            1,
            false,
            false,
            true,
            IssueTrigger::unknown(),
        );

        $this->assertTrue($event->ignoredByTest());
        $this->assertSame('Test Triggered PHP Deprecation (FooTest::testBar, unknown if issue was triggered in first-party code or third-party code, ignored by test) in file:1' . PHP_EOL . 'message', $event->asString());
    }

    public function testCanBeSuppressed(): void
    {
        $event = new PhpDeprecationTriggered(
            $this->telemetryInfo(),
            $this->testValueObject(),
            'message',
            'file',
            1,
            true,
            false,
            false,
            IssueTrigger::unknown(),
        );

        $this->assertTrue($event->wasSuppressed());
        $this->assertSame('Test Triggered PHP Deprecation (FooTest::testBar, unknown if issue was triggered in first-party code or third-party code, suppressed using operator) in file:1' . PHP_EOL . 'message', $event->asString());
    }
}
