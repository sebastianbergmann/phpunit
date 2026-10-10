<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased;

use Closure;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\EventsAreAlreadyBeingCollectedException;
use PHPUnit\Event\Facade;

/**
 * Runs code with the events that it emits collected rather than dispatched,
 * so that the events of an example of a property that a test runs are not
 * taken for those of the test.
 *
 * Collecting events is not re-entrant, and a test that is retried runs while
 * its events are collected: these are put aside meanwhile.
 */
final class EventCollector
{
    /**
     * @param Closure(): void $run
     */
    public static function collect(Closure $run): EventCollection
    {
        $facade = Facade::instance();
        $outer  = null;

        try {
            $facade->startCollectingEvents();
        } catch (EventsAreAlreadyBeingCollectedException) {
            $outer = $facade->stopCollectingEvents();

            $facade->startCollectingEvents();
        }

        try {
            $run();
        } finally {
            $events = $facade->stopCollectingEvents();

            if ($outer !== null) {
                $facade->startCollectingEvents();
                $facade->forward($outer);
            }
        }

        return $events;
    }
}
