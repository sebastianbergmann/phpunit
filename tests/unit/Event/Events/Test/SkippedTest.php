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

use Exception;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Skipped::class)]
final class SkippedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();
        $throwable     = Throwable::from(new Exception);
        $message       = 'skipped';

        $event = new Skipped(
            $telemetryInfo,
            $test,
            $throwable,
            $message
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
        $this->assertSame($message, $event->message());
    }
}
