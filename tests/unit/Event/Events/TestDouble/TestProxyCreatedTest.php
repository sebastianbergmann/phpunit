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

#[CoversClass(TestProxyCreated::class)]
#[Small]
final class TestProxyCreatedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo        = $this->telemetryInfo();
        $className            = 'OriginalType';
        $constructorArguments = 'exported constructor arguments';

        $event = new TestProxyCreated(
            $telemetryInfo,
            $className,
            $constructorArguments,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($className, $event->className());
        $this->assertSame($constructorArguments, $event->constructorArguments());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new TestProxyCreated(
            $this->telemetryInfo(),
            'OriginalType',
            'exported constructor arguments',
        );

        $this->assertSame('Test Proxy Created (OriginalType)', $event->asString());
    }
}
