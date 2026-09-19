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

use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

#[CoversClass(Facade::class)]
#[Small]
#[Group('event-system')]
final class FacadeTest extends TestCase
{
    public function testSubscriberRegistrationDoesNotWorkWhenEventFacadeIsSealed(): void
    {
        $this->expectException(EventFacadeIsSealedException::class);

        Facade::instance()->registerSubscriber(
            new class implements Subscriber
            {},
        );
    }

    public function testUsesTheIsolationDispatcherOfAFacadeThatWasInitializedForIsolation(): void
    {
        // In a process whose event facade was initialized for isolation — the
        // worker process of a parallel test run, for example — the emitter
        // dispatches to the isolation dispatcher, so the collection windows
        // must be opened on that dispatcher and not on the deferring one.
        $facade = new Facade;

        $dispatcher = new CollectingDispatcher(
            new DirectDispatcher(new ReflectionMethod(Facade::class, 'typeMap')->invoke($facade)),
        );

        new ReflectionProperty(Facade::class, 'isolationDispatcher')->setValue($facade, $dispatcher);

        $facade->startCollectingEvents();

        $this->expectException(EventsAreAlreadyBeingCollectedException::class);

        $dispatcher->startCollectingEvents();
    }

    public function testKeepsTheEmitterWhenInitializedForIsolationRepeatedly(): void
    {
        // A persistent worker process of a parallel test run initializes its
        // event facade for isolation once per unit, but the error handler and
        // the code coverage singletons hold the emitter from when the worker
        // booted, so the emitter must keep its identity across those calls.
        $facade   = new Facade;
        $property = new ReflectionProperty(Facade::class, 'emitter');
        $emitter  = $property->getValue($facade);

        $first  = $facade->initForIsolation(HRTime::fromSecondsAndNanoseconds(1, 0));
        $second = $facade->initForIsolation(HRTime::fromSecondsAndNanoseconds(2, 0));

        $this->assertSame($emitter, $property->getValue($facade));
        $this->assertNotSame($first, $second);
    }

    public function testTracerRegistrationDoesNotWorkWhenEventFacadeIsSealed(): void
    {
        $this->expectException(EventFacadeIsSealedException::class);

        Facade::instance()->registerTracer(
            new class implements Tracer
            {
                public function trace(Event $event): void
                {
                }
            },
        );
    }
}
