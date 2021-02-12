<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Tracer;

use EventWithFixedStringRepresentation;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\ResultPrinter;

/**
 * @internal
 *
 * @covers \PHPUnit\Event\Tracer\PrintingTracer
 */
final class PrintingTracerTest extends TestCase
{
    public function testTraceWritesEventAsStringWithPrinter(): void
    {
        $stringRepresentation = 'Hello, I am an event';

        $event = new EventWithFixedStringRepresentation($stringRepresentation);

        $printer = $this->createMock(ResultPrinter::class);

        $printer
            ->expects($this->once())
            ->method('write')
            ->with($this->identicalTo($stringRepresentation));

        $tracer = new PrintingTracer($printer);

        $tracer->trace($event);
    }
}
