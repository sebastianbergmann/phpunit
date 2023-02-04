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

#[CoversClass(Skipped::class)]
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
}
