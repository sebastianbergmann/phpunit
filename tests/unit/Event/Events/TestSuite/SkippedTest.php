<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Skipped::class)]
#[Small]
final class SkippedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testSuite     = $this->testSuiteValueObject();
        $message       = 'the-message';

        $event = new Skipped($telemetryInfo, $testSuite, $message);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($testSuite, $event->testSuite());
        $this->assertSame($message, $event->message());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Skipped(
            $this->telemetryInfo(),
            $this->testSuiteValueObject(),
            'the-message',
        );

        $this->assertSame('Test Suite Skipped (foo, the-message)', $event->asString());
    }
}
