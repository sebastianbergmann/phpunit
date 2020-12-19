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
use PHPUnit\Event\Code;

/**
 * @covers \PHPUnit\Event\Test\Failed
 */
final class FailedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = self::createTelemetryInfo();
        $test          = new Code\Test(...array_values(explode(
            '::',
            __METHOD__
        )));
        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

        $event = new Failed(
            $telemetryInfo,
            $test,
            $message
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }
}
