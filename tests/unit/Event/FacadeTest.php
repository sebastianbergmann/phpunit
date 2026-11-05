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

use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

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

    public function testCollectsEventsWithoutMeasuringTheirTelemetry(): void
    {
        $facade  = Facade::instance();
        $emitter = Facade::emitter();
        $test    = $this->valueObjectForEvents();

        $facade->startCollectingEventsWithoutTelemetry();

        try {
            $emitter->testPreparationStarted($test);
            $emitter->testPreparationStarted($test);
        } finally {
            $withoutTelemetry = $facade->stopCollectingEvents()->asArray();
        }

        $facade->startCollectingEvents();

        try {
            $emitter->testPreparationStarted($test);
        } finally {
            $withTelemetry = $facade->stopCollectingEvents()->asArray();
        }

        $this->assertCount(2, $withoutTelemetry);
        $this->assertCount(1, $withTelemetry);
        $this->assertSame($withoutTelemetry[0]->telemetryInfo(), $withoutTelemetry[1]->telemetryInfo());
        $this->assertNotSame($withoutTelemetry[1]->telemetryInfo(), $withTelemetry[0]->telemetryInfo());
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
