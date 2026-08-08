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

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap));

        $this->assertEmpty($dispatcher->flush());
    }

    public function testCollectsDispatchedEventsUntilFlushed(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap));
        $event      = $this->createStub(Event::class);

        $dispatcher->dispatch($event);

        $this->assertSame([$event], $dispatcher->flush()->asArray());
    }

    public function testDispatchesCollectedEventsToRegisteredSubscribersButNotEventsDivertedByACollectionWindow(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);
        $typeMap->addMapping(TestRunner\WarningTriggeredSubscriber::class, TestRunner\WarningTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap));

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
}
