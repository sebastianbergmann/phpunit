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
use stdClass;

/**
 * @covers \PHPUnit\Event\TestDouble\MockObjectCreatedFromWsdl
 */
final class MockObjectCreatedFromWsdlTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo     = self::createTelemetryInfo();
        $wsdlFile          = __FILE__;
        $originalClassName = self::class;
        $mockClassName     = stdClass::class;
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

        $event = new MockObjectCreatedFromWsdl(
            $telemetryInfo,
            $wsdlFile,
            $originalClassName,
            $mockClassName,
            $methods,
            $callOriginalConstructor,
            $options
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($wsdlFile, $event->wsdlFile());
        $this->assertSame($originalClassName, $event->originalClassName());
        $this->assertSame($mockClassName, $event->mockClassName());
        $this->assertSame($methods, $event->methods());
        $this->assertSame($callOriginalConstructor, $event->callOriginalConstructor());
        $this->assertSame($options, $event->options());
    }
}
