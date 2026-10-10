<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\GarbageCollection;

use function array_filter;
use function count;
use Closure;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Event;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestRunner\GarbageCollectionTriggered;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestFixture\PropertyBased\EventCollector;

#[CoversClass(GarbageCollectionHandler::class)]
#[Small]
#[Group('test-runner')]
final class GarbageCollectionHandlerTest extends AbstractEventTestCase
{
    public function testCollectsGarbageEachTimeTheConfiguredNumberOfTestsHasFinished(): void
    {
        $this->assertSame(
            2,
            $this->numberOfGarbageCollections(
                function (GarbageCollectionHandler $handler): void
                {
                    $handler->testFinished($this->finished('testOne'));
                    $handler->testFinished($this->finished('testTwo'));
                    $handler->testFinished($this->finished('testThree'));
                    $handler->testFinished($this->finished('testFour'));
                },
            ),
        );
    }

    public function testDoesNotCountATestAgainThatWasCountedWhileItsEventsWereCollected(): void
    {
        $this->assertSame(
            1,
            $this->numberOfGarbageCollections(
                function (GarbageCollectionHandler $handler): void
                {
                    $handler->testFinishedWhileEventsAreCollected($this->finished('testOne'));
                    $handler->testFinished($this->finished('testOne'));
                    $handler->testFinished($this->finished('testTwo'));
                    $handler->testFinished($this->finished('testThree'));
                },
            ),
        );
    }

    public function testCountsATestAgainWhoseCollectedEventsWereNotForwardedBeforeAnotherTestFinished(): void
    {
        $this->assertSame(
            2,
            $this->numberOfGarbageCollections(
                function (GarbageCollectionHandler $handler): void
                {
                    // the events of an attempt that is retried are not forwarded
                    $handler->testFinishedWhileEventsAreCollected($this->finished('testOne'));
                    $handler->testFinished($this->finished('testTwo'));
                    $handler->testFinished($this->finished('testOne'));
                    $handler->testFinished($this->finished('testThree'));
                },
            ),
        );
    }

    /**
     * @param Closure(GarbageCollectionHandler): void $run
     */
    private function numberOfGarbageCollections(Closure $run): int
    {
        $events = EventCollector::collect(
            static function () use ($run): void
            {
                $run(new GarbageCollectionHandler(new Facade, 2));
            },
        );

        return count(
            array_filter(
                $events->asArray(),
                static fn (Event $event): bool => $event instanceof GarbageCollectionTriggered,
            ),
        );
    }

    /**
     * @param non-empty-string $methodName
     */
    private function finished(string $methodName): Finished
    {
        return new Finished(
            $this->telemetryInfo(),
            new TestMethod(
                'FooTest',
                $methodName,
                'FooTest.php',
                1,
                TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
                MetadataCollection::fromArray([]),
                TestDataCollection::fromArray([]),
            ),
            1,
        );
    }
}
