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

#[CoversClass(AssertionSucceeded::class)]
final class AssertionSucceededTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $constraint    = 'constraint';
        $count         = 1;
        $message       = 'message';

        $event = new AssertionSucceeded(
            $telemetryInfo,
            '',
            $constraint,
            $count,
            $message,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame('', $event->value());
        $this->assertSame($count, $event->count());
        $this->assertSame($message, $event->message());
        $this->assertSame('Assertion Succeeded (Constraint: constraint, Message: message)', $event->asString());
    }
}
