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
use PHPUnit\TestFixture;

/**
 * @covers \PHPUnit\Event\TestDouble\MockForTraitCreated
 */
final class MockForTraitCreatedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = self::createTelemetryInfo();
        $traitName     = TestFixture\ExampleTrait::class;

        $event = new MockForTraitCreated(
            $telemetryInfo,
            $traitName
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($traitName, $event->traitName());
    }
}
