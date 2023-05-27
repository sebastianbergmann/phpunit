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

#[CoversClass(MockObjectForAbstractClassCreated::class)]
#[Small]
final class MockObjectForAbstractClassCreatedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $className     = 'OriginalType';

        $event = new MockObjectForAbstractClassCreated(
            $telemetryInfo,
            $className,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($className, $event->className());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new MockObjectForAbstractClassCreated(
            $this->telemetryInfo(),
            'OriginalType',
        );

        $this->assertSame('Mock Object Created (OriginalType)', $event->asString());
    }
}
