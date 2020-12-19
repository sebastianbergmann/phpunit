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
 * @covers \PHPUnit\Event\Test\SkippedByDataProvider
 */
final class SkippedByDataProviderTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = self::createTelemetryInfo();
        $testMethod    = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));
        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

        $event = new SkippedByDataProvider(
            $telemetryInfo,
            $testMethod,
            $message
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($testMethod, $event->testMethod());
        $this->assertSame($message, $event->message());
    }
}
