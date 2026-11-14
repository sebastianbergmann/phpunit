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

use LogicException;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
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

    public function testRegistrationOfSubscribersForCollectedEventsDoesNotWorkWhenEventFacadeIsSealed(): void
    {
        $this->expectException(EventFacadeIsSealedException::class);

        Facade::instance()->registerSubscribersForCollectedEvents(
            new class implements Subscriber
            {},
        );
    }

    public function testStopsMeasuringTheTelemetryOfEvents(): void
    {
        $tracer = new class implements Tracer
        {
            /**
             * @var list<Telemetry\Info>
             */
            public array $telemetry = [];

            public function trace(Event $event): void
            {
                if ($event instanceof Test\PreparationStarted) {
                    $this->telemetry[] = $event->telemetryInfo();
                }
            }
        };

        $facade = new Facade;

        $facade->registerTracer($tracer);
        $facade->seal();

        // the event facade of the test run is swapped for this one, as the
        // emitter is taken from the instance of the event facade, and the
        // telemetry of the events of the test run must still be measured
        $property = new ReflectionProperty(Facade::class, 'instance');
        $instance = $property->getValue();

        $property->setValue(null, $facade);

        try {
            $test = $this->valueObjectForEvents();

            Facade::emitter()->testPreparationStarted($test);

            $facade->stopMeasuringTelemetry();

            Facade::emitter()->testPreparationStarted($test);
            Facade::emitter()->testPreparationStarted($test);
        } finally {
            $property->setValue(null, $instance);
        }

        [$measured, $first, $second] = $tracer->telemetry;

        $this->assertSame($measured, $first);
        $this->assertSame($first, $second);
    }

    public function testCollectsTheEventsThatAreEmittedWhileItsClosureRuns(): void
    {
        [$facade, $tracer] = $this->sealedFacadeWithTracer();

        [$event] = $tracer->events;

        $events = new EventCollection;

        $events->add($event);

        $collected = $facade->collectingEvents(
            static function () use ($facade, $events): void
            {
                $facade->forward($events);
            },
        );

        $this->assertSame([$event], $collected->asArray());
        $this->assertSame([$event], $tracer->events);
    }

    public function testForwardsTheEventsThatWereCollectedBeforeItsClosureRaisedAThrowableAndRaisesIt(): void
    {
        [$facade, $tracer] = $this->sealedFacadeWithTracer();

        [$event] = $tracer->events;

        $events = new EventCollection;

        $events->add($event);

        $exception = new LogicException;

        try {
            $facade->collectingEvents(
                static function () use ($facade, $events, $exception): void
                {
                    $facade->forward($events);

                    throw $exception;
                },
            );

            $this->fail('The exception was not raised');
        } catch (LogicException $e) {
            $this->assertSame($exception, $e);
        }

        $this->assertSame([$event, $event], $tracer->events);
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

    /**
     * @return array{0: Facade, 1: object{events: list<Event>}&Tracer}
     */
    private function sealedFacadeWithTracer(): array
    {
        $tracer = new class implements Tracer
        {
            /**
             * @var list<Event>
             */
            public array $events = [];

            public function trace(Event $event): void
            {
                $this->events[] = $event;
            }
        };

        $facade = new Facade;

        $facade->registerTracer($tracer);
        $facade->seal();

        return [$facade, $tracer];
    }
}
