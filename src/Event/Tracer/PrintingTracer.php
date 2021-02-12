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

use PHPUnit\Event\Event;
use PHPUnit\TextUI\ResultPrinter;

final class PrintingTracer implements Tracer
{
    private ResultPrinter $resultPrinter;

    public function __construct(ResultPrinter $resultPrinter)
    {
        $this->resultPrinter = $resultPrinter;
    }

    public function trace(Event $event): void
    {
        $this->resultPrinter->write($event->asString());
    }
}
