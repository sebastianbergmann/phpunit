<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

/**
 * An emitter that collects the events emitted through it into an event
 * collection that can later be flushed and replayed, without disturbing the
 * global event facade.
 *
 * Several of these can be in use at the same time in one process, which is what
 * lets the parallel test runner run several PHPT tests concurrently in the main
 * process, each collecting its own events for deterministic, suite-ordered
 * replay through Facade::forward().
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CollectingEmitter
{
    private Emitter $emitter;
    private CollectingDispatcher $dispatcher;

    public function __construct(Emitter $emitter, CollectingDispatcher $dispatcher)
    {
        $this->emitter    = $emitter;
        $this->dispatcher = $dispatcher;
    }

    public function emitter(): Emitter
    {
        return $this->emitter;
    }

    public function flush(): EventCollection
    {
        return $this->dispatcher->flush();
    }
}
