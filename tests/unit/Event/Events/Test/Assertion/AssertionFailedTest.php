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

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(AssertionFailed::class)]
#[Small]
final class AssertionFailedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $value         = 'value';
        $constraint    = 'constraint';
        $count         = 1;
        $message       = 'message';

        $event = new AssertionFailed(
            $telemetryInfo,
            $value,
            $constraint,
            $count,
            $message,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($value, $event->value());
        $this->assertSame($count, $event->count());
        $this->assertSame($message, $event->message());
        $this->assertSame('Assertion Failed (Constraint: constraint, Value: value, Message: message)', $event->asString());
    }
}
