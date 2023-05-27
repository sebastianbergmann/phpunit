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

#[CoversClass(MockObjectFromWsdlCreated::class)]
#[Small]
final class MockObjectFromWsdlCreatedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo     = $this->telemetryInfo();
        $wsdlFile          = 'test.wsdl';
        $originalClassName = 'OriginalClassName';
        $mockClassName     = 'MockClassName';
        $methods           = [
            'foo',
            'bar',
        ];
        $callOriginalConstructor = false;
        $options                 = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 9000,
        ];

        $event = new MockObjectFromWsdlCreated(
            $telemetryInfo,
            $wsdlFile,
            $originalClassName,
            $mockClassName,
            $methods,
            $callOriginalConstructor,
            $options,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($wsdlFile, $event->wsdlFile());
        $this->assertSame($originalClassName, $event->originalClassName());
        $this->assertSame($mockClassName, $event->mockClassName());
        $this->assertSame($methods, $event->methods());
        $this->assertSame($callOriginalConstructor, $event->callOriginalConstructor());
        $this->assertSame($options, $event->options());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new MockObjectFromWsdlCreated(
            $this->telemetryInfo(),
            'test.wsdl',
            'OriginalClassName',
            'MockClassName',
            [],
            false,
            [],
        );

        $this->assertSame('Mock Object Created (test.wsdl)', $event->asString());
    }
}
