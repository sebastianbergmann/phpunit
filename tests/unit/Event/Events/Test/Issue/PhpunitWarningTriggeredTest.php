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

#[CoversClass(PhpunitWarningTriggered::class)]
#[Small]
final class PhpunitWarningTriggeredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();
        $message       = 'message';
        $ignoredByTest = false;

        $event = new PhpunitWarningTriggered(
            $telemetryInfo,
            $test,
            $message,
            $ignoredByTest,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($ignoredByTest, $event->ignoredByTest());
        $this->assertSame('Test Triggered PHPUnit Warning (FooTest::testBar)' . PHP_EOL . 'message', $event->asString());
    }

    public function testCanBeIgnoredByTest(): void
    {
        $event = new PhpunitWarningTriggered(
            $this->telemetryInfo(),
            $this->testValueObject(),
            'message',
            true,
        );

        $this->assertTrue($event->ignoredByTest());
        $this->assertSame('Test Triggered PHPUnit Warning (FooTest::testBar, ignored by test)' . PHP_EOL . 'message', $event->asString());
    }
}
