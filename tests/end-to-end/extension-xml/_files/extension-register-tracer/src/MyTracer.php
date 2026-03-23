<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\RegisterTracer;

use const PHP_EOL;
use PHPUnit\Event\Event;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\Tracer\Tracer;

final class MyTracer implements Tracer
{
    public function trace(Event $event): void
    {
        if ($event instanceof ExecutionFinished) {
            print 'Tracer received: ' . $event->asString() . PHP_EOL;
        }
    }
}
