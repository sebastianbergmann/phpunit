<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Assertion;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Constraint;

/**
 * @covers \PHPUnit\Event\Assertion\Made
 */
final class MadeTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = self::createTelemetryInfo();
        $value         = 'Hmm';
        $constraint    = new Constraint\IsEqual('Ok');
        $message       = 'Well, that did not go as planned!';
        $hasFailed     = true;

        $event = new Made(
            $telemetryInfo,
            $value,
            $constraint,
            $message,
            $hasFailed
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($value, $event->value());
        $this->assertSame($constraint, $event->constraint());
        $this->assertSame($message, $event->message());
        $this->assertSame($hasFailed, $event->hasFailed());
    }
}
