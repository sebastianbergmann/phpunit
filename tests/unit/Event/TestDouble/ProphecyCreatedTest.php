<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestDouble;

use PHPUnit\Event\AbstractEventTestCase;

/**
 * @covers \PHPUnit\Event\TestDouble\ProphecyCreated
 */
final class ProphecyCreatedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = self::createTelemetryInfo();

        $event = new ProphecyCreated($telemetryInfo);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
    }
}
