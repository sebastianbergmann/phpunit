<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function hrtime;
use function iterator_to_array;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Telemetry;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\TestRunner\PhpunitWarningTriggered;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use WeakReference;

#[CoversClass(EventSpool::class)]
#[Small]
final class EventSpoolTest extends TestCase
{
    public function testHandsOutTheEventsOfAUnitInTheOrderInWhichTheyWereAdded(): void
    {
        $spool = new EventSpool;

        $spool->add(1, $this->events('first'), '');
        $spool->add(1, $this->events('second'), '');

        $this->assertSame(['first', 'second'], $this->messagesOf($spool->take(1)));
    }

    public function testHandsOutWhatTheWorkerWroteBeforeItStreamedTheEvents(): void
    {
        $spool = new EventSpool;

        $spool->add(1, $this->events('first'), 'output before first');
        $spool->add(1, $this->events('second'), '');

        $output = [];

        foreach ($spool->take(1) as [$events, $outputBeforeEvents]) {
            $output[] = $outputBeforeEvents;
        }

        $this->assertSame(['output before first', ''], $output);
    }

    public function testKeepsTheEventsOfEachUnitApart(): void
    {
        $spool = new EventSpool;

        $spool->add(1, $this->events('first of unit 1'), '');
        $spool->add(2, $this->events('first of unit 2'), '');
        $spool->add(1, $this->events('second of unit 1'), '');

        $this->assertSame(['first of unit 2'], $this->messagesOf($spool->take(2)));
        $this->assertSame(['first of unit 1', 'second of unit 1'], $this->messagesOf($spool->take(1)));
    }

    public function testNoLongerHasTheEventsOfAUnitOnceTheyWereHandedOut(): void
    {
        $spool = new EventSpool;

        $this->assertFalse($spool->has(1));

        $spool->add(1, $this->events('first'), '');

        $this->assertTrue($spool->has(1));

        $this->messagesOf($spool->take(1));

        $this->assertFalse($spool->has(1));
        $this->assertSame([], $this->messagesOf($spool->take(1)));
    }

    public function testDiscardsTheEventsOfAUnit(): void
    {
        $spool = new EventSpool;

        $spool->add(1, $this->events('first of unit 1'), '');
        $spool->add(2, $this->events('first of unit 2'), '');

        $spool->discard(1);

        $this->assertFalse($spool->has(1));
        $this->assertSame([], $this->messagesOf($spool->take(1)));
        $this->assertSame(['first of unit 2'], $this->messagesOf($spool->take(2)));
    }

    public function testKeepsEventsThatAreAddedAfterNothingWasKeptAnymore(): void
    {
        $spool = new EventSpool;

        $spool->add(1, $this->events('handed out'), '');
        $spool->add(2, $this->events('discarded'), '');

        $this->messagesOf($spool->take(1));

        $spool->discard(2);

        $spool->add(3, $this->events('first'), '');
        $spool->add(3, $this->events('second'), '');

        $this->assertSame(['first', 'second'], $this->messagesOf($spool->take(3)));
    }

    public function testDoesNotKeepTheEventsInMemory(): void
    {
        $spool  = new EventSpool;
        $events = $this->events('first');

        $reference = WeakReference::create($events);

        $spool->add(1, $events, '');

        unset($events);

        $this->assertNull($reference->get());
        $this->assertSame(['first'], $this->messagesOf($spool->take(1)));
    }

    /**
     * @param iterable<array{0: EventCollection, 1: string}> $collections
     *
     * @return list<string>
     */
    private function messagesOf(iterable $collections): array
    {
        $messages = [];

        foreach (iterator_to_array($collections, false) as [$events, $output]) {
            foreach ($events as $event) {
                $this->assertInstanceOf(PhpunitWarningTriggered::class, $event);

                $messages[] = $event->message();
            }
        }

        return $messages;
    }

    private function events(string $message): EventCollection
    {
        $events = new EventCollection;

        $events->add(
            new PhpunitWarningTriggered(
                new Telemetry\Info(
                    new Telemetry\Snapshot(
                        HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                        Telemetry\MemoryUsage::fromBytes(1000),
                        Telemetry\MemoryUsage::fromBytes(2000),
                        new Telemetry\GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                        Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                        Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                        Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                    ),
                    Telemetry\Duration::fromSecondsAndNanoseconds(123, 456),
                    Telemetry\MemoryUsage::fromBytes(2000),
                    Telemetry\Duration::fromSecondsAndNanoseconds(234, 567),
                    Telemetry\MemoryUsage::fromBytes(3000),
                    Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                    Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                    Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                    Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                    Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                    Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                    12345,
                ),
                $message,
            ),
        );

        return $events;
    }
}
