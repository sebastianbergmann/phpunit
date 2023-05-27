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

#[CoversClass(MockObjectForIntersectionOfInterfacesCreated::class)]
#[Small]
final class MockObjectForIntersectionOfInterfacesCreatedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $interfaces    = ['AnInterface', 'AnotherInterface'];

        $event = new MockObjectForIntersectionOfInterfacesCreated(
            $telemetryInfo,
            $interfaces,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($interfaces, $event->interfaces());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new MockObjectForIntersectionOfInterfacesCreated(
            $this->telemetryInfo(),
            ['AnInterface', 'AnotherInterface'],
        );

        $this->assertSame('Mock Object Created (AnInterface&AnotherInterface)', $event->asString());
    }
}
