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

#[CoversClass(PartialMockObjectCreated::class)]
#[Small]
final class PartialMockObjectCreatedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $className     = 'OriginalType';
        $methodNames   = [
            'foo',
            'bar',
            'baz',
        ];

        $event = new PartialMockObjectCreated(
            $telemetryInfo,
            $className,
            ...$methodNames,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($className, $event->className());
        $this->assertSame($methodNames, $event->methodNames());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new PartialMockObjectCreated(
            $this->telemetryInfo(),
            'OriginalType',
        );

        $this->assertSame('Partial Mock Object Created (OriginalType)', $event->asString());
    }
}
