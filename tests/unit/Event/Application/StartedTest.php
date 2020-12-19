<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Application;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\TestRunner\Started;

/**
 * @covers \PHPUnit\Event\TestRunner\Started
 */
final class StartedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = self::createTelemetryInfo();
        $runtime       = new Runtime();

        $event = new Started(
            $telemetryInfo,
            $runtime
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($runtime, $event->runtime());
    }
}
