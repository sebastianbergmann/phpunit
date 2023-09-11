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

#[CoversClass(DeprecationTriggered::class)]
#[Small]
final class DeprecationTriggeredTest extends AbstractEventTestCase
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

        $event = new DeprecationTriggered(
            $telemetryInfo,
            $test,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame($ignoredByBaseline, $event->ignoredByBaseline());
        $this->assertSame('Test Triggered Deprecation (FooTest::testBar)' . PHP_EOL . 'message', $event->asString());
    }

    public function testCanBeIgnoredByBaseline(): void
    {
        $event = new DeprecationTriggered(
            $this->telemetryInfo(),
            $this->testValueObject(),
            'message',
            'file',
            1,
            false,
            true,
        );

        $this->assertTrue($event->ignoredByBaseline());
        $this->assertSame('Test Triggered Baseline-Ignored Deprecation (FooTest::testBar)' . PHP_EOL . 'message', $event->asString());
    }

    public function testCanBeSuppressed(): void
    {
        $event = new DeprecationTriggered(
            $this->telemetryInfo(),
            $this->testValueObject(),
            'message',
            'file',
            1,
            true,
            false,
        );

        $this->assertTrue($event->wasSuppressed());
        $this->assertSame('Test Triggered Suppressed Deprecation (FooTest::testBar)' . PHP_EOL . 'message', $event->asString());
    }
}
