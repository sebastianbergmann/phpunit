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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\DummyEvent;
use PHPUnit\TestFixture\DummySubscriber;

#[CoversClass(CollectingDispatcher::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/dispatcher')]
final class CollectingDispatcherTest extends AbstractEventTestCase
{
    public function testHasNoCollectedEventsWhenFlushedImmediatelyAfterCreation(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap), new DirectDispatcher($typeMap));

        $this->assertEmpty($dispatcher->flush());
    }

    public function testCollectsDispatchedEventsUntilFlushed(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap), new DirectDispatcher($typeMap));
        $event      = $this->createStub(Event::class);

        $dispatcher->dispatch($event);

        $this->assertSame([$event], $dispatcher->flush()->asArray());
    }

    public function testDispatchesCollectedEventsToRegisteredSubscribers(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);
        $typeMap->addMapping(DummySubscriber::class, DummyEvent::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap), new DirectDispatcher($typeMap));
        $event      = new DummyEvent;
        $subscriber = $this->createMock(DummySubscriber::class);

        $subscriber
            ->expects($this->once())
            ->method('notify')
            ->with($this->identicalTo($event))
            ->seal();

        $dispatcher->registerSubscriber($subscriber);

        $dispatcher->dispatch($event);

        $this->assertSame([$event], $dispatcher->flush()->asArray());
    }

    public function testRejectsSubscriberOfUnknownType(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap), new DirectDispatcher($typeMap));

        $this->expectException(UnknownSubscriberTypeException::class);

        $dispatcher->registerSubscriber($this->createStub(DummySubscriber::class));
    }

    public function testDispatchesCollectedEventsToRegisteredSubscribersButNotEventsDivertedByACollectionWindow(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);
        $typeMap->addMapping(TestRunner\WarningTriggeredSubscriber::class, TestRunner\WarningTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap), new DirectDispatcher($typeMap));

        $messages = [];

        $dispatcher->registerSubscriber(
            new class($messages) implements TestRunner\WarningTriggeredSubscriber
            {
                /**
                 * @var list<string>
                 */
                private array $messages;

                /**
                 * @param list<string> $messages
                 */
                public function __construct(array &$messages)
                {
                    $this->messages = &$messages;
                }

                public function notify(TestRunner\WarningTriggered $event): void
                {
                    $this->messages[] = $event->message();
                }
            },
        );

        $dispatcher->dispatch(new TestRunner\WarningTriggered($this->telemetryInfo(), 'collected'));

        $this->assertSame(['collected'], $messages);

        // An event diverted by a collection window does not become part of the
        // recorded stream and must not reach the subscriber either.
        $dispatcher->startCollectingEvents();

        $dispatcher->dispatch(new TestRunner\WarningTriggered($this->telemetryInfo(), 'diverted'));

        $this->assertSame(['collected'], $messages);
        $this->assertCount(1, $dispatcher->stopCollectingEvents());
    }

    public function testDispatchesEventsDivertedByACollectionWindowToSubscribersForCollectedEvents(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);
        $typeMap->addMapping(TestRunner\WarningTriggeredSubscriber::class, TestRunner\WarningTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap), new DirectDispatcher($typeMap));

        $messages = [];

        $dispatcher->registerSubscriberForCollectedEvents(
            new class($messages) implements TestRunner\WarningTriggeredSubscriber
            {
                /**
                 * @var list<string>
                 */
                private array $messages;

                /**
                 * @param list<string> $messages
                 */
                public function __construct(array &$messages)
                {
                    $this->messages = &$messages;
                }

                public function notify(TestRunner\WarningTriggered $event): void
                {
                    $this->messages[] = $event->message();
                }
            },
        );

        $dispatcher->dispatch(new TestRunner\WarningTriggered($this->telemetryInfo(), 'collected'));

        $this->assertSame([], $messages);

        $dispatcher->startCollectingEvents();

        $dispatcher->dispatch(new TestRunner\WarningTriggered($this->telemetryInfo(), 'diverted'));

        $this->assertSame(['diverted'], $messages);
        $this->assertCount(1, $dispatcher->stopCollectingEvents());
        $this->assertCount(1, $dispatcher->flush());
    }

    public function testDivertsEventOfUnknownTypeWithoutDispatchingItToSubscribersForCollectedEvents(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap), new DirectDispatcher($typeMap));
        $event      = $this->createStub(Event::class);

        $dispatcher->startCollectingEvents();

        $dispatcher->dispatch($event);

        $this->assertSame([$event], $dispatcher->stopCollectingEvents()->asArray());
    }

    public function testRejectsSubscriberForCollectedEventsOfUnknownType(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap), new DirectDispatcher($typeMap));

        $this->expectException(UnknownSubscriberTypeException::class);

        $dispatcher->registerSubscriberForCollectedEvents($this->createStub(DummySubscriber::class));
    }
}
