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
use function pack;
use function serialize;
use function strlen;
use function substr;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Telemetry;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\TestRunner\PhpunitWarningTriggered;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventStream::class)]
#[Small]
final class EventStreamTest extends TestCase
{
    private const string NONCE = '0123456789abcdef';

    public function testReadsBackTheFramesItWrote(): void
    {
        $data = $this->frames('first', 'second');

        $result = EventStream::readFrames($data, self::NONCE);

        $this->assertFalse($result['tainted']);
        $this->assertSame(strlen($data), $result['bytesConsumed']);
        $this->assertSame(['first', 'second'], $this->messagesOf($result['frames']));
    }

    public function testReadsBackHowMuchTheWorkerHadWrittenWhenItWroteAFrame(): void
    {
        $first = new EventCollection;
        $first->add($this->warningTriggered('first'));

        $second = new EventCollection;
        $second->add($this->warningTriggered('second'));

        $data = EventStream::frame(self::NONCE, $first, 12) . EventStream::frame(self::NONCE, $second, 345);

        $result = EventStream::readFrames($data, self::NONCE);

        $this->assertFalse($result['tainted']);
        $this->assertCount(2, $result['frames']);
        $this->assertSame(12, $result['frames'][0]['outputLength']);
        $this->assertSame(345, $result['frames'][1]['outputLength']);
    }

    public function testEncodesAnEmptyCollectionOfEventsToAnEmptyFrame(): void
    {
        $this->assertSame('', EventStream::frame(self::NONCE, new EventCollection, 0));
    }

    public function testLeavesAFrameWhoseBytesAreNotYetAllVisibleForALaterRead(): void
    {
        $firstFrame = $this->frames('first');
        $data       = $firstFrame . substr($this->frames('second'), 0, -1);

        $result = EventStream::readFrames($data, self::NONCE);

        $this->assertFalse($result['tainted']);
        $this->assertSame(strlen($firstFrame), $result['bytesConsumed']);
        $this->assertSame(['first'], $this->messagesOf($result['frames']));
    }

    public function testLeavesAFrameWhoseLengthPrefixIsNotYetCompletelyVisibleForALaterRead(): void
    {
        $result = EventStream::readFrames(substr($this->frames('first'), 0, 4), self::NONCE);

        $this->assertFalse($result['tainted']);
        $this->assertSame(0, $result['bytesConsumed']);
        $this->assertSame([], $result['frames']);
    }

    public function testReadsNothingFromAnEmptyStream(): void
    {
        $result = EventStream::readFrames('', self::NONCE);

        $this->assertFalse($result['tainted']);
        $this->assertSame(0, $result['bytesConsumed']);
        $this->assertSame([], $result['frames']);
    }

    public function testTaintsTheStreamAtAFrameWhoseNonceDoesNotMatch(): void
    {
        $result = EventStream::readFrames($this->frames('first'), 'a-different-nonce');

        $this->assertTrue($result['tainted']);
        $this->assertSame([], $result['frames']);
    }

    public function testTaintsTheStreamAtAFrameThatDoesNotSayHowMuchTheWorkerHadWritten(): void
    {
        $payload = self::NONCE . 'short';
        $data    = pack('J', strlen($payload)) . $payload;

        $result = EventStream::readFrames($data, self::NONCE);

        $this->assertTrue($result['tainted']);
        $this->assertSame([], $result['frames']);
    }

    public function testTaintsTheStreamAtAFrameWhoseOutputLengthOverflows(): void
    {
        $events = new EventCollection;
        $events->add($this->warningTriggered('first'));

        $payload = self::NONCE . pack('J', -1) . serialize($events);
        $data    = pack('J', strlen($payload)) . $payload;

        $result = EventStream::readFrames($data, self::NONCE);

        $this->assertTrue($result['tainted']);
        $this->assertSame([], $result['frames']);
    }

    public function testTaintsTheStreamAtAFrameWhosePayloadIsNotACollectionOfEvents(): void
    {
        $payload = self::NONCE . pack('J', 0) . 'not-a-serialized-collection-of-events';
        $data    = pack('J', strlen($payload)) . $payload;

        $result = EventStream::readFrames($data, self::NONCE);

        $this->assertTrue($result['tainted']);
        $this->assertSame([], $result['frames']);
    }

    public function testTaintsTheStreamAtAFrameWhoseLengthPrefixOverflows(): void
    {
        $result = EventStream::readFrames(pack('J', -1) . 'xx', self::NONCE);

        $this->assertTrue($result['tainted']);
        $this->assertSame([], $result['frames']);
    }

    public function testReturnsTheFramesThatPrecedeTheFrameThatTaintsTheStream(): void
    {
        $data = $this->frames('first') . pack('J', 4) . 'xxxx';

        $result = EventStream::readFrames($data, self::NONCE);

        $this->assertTrue($result['tainted']);
        $this->assertSame(['first'], $this->messagesOf($result['frames']));
    }

    /**
     * A stream of one frame per given message, encoded through frame(), each
     * frame carrying one test runner warning event with that message.
     */
    private function frames(string ...$messages): string
    {
        $data = '';

        foreach ($messages as $message) {
            $events = new EventCollection;

            $events->add($this->warningTriggered($message));

            $data .= EventStream::frame(self::NONCE, $events, 0);
        }

        return $data;
    }

    /**
     * @param list<array{events: EventCollection, outputLength: non-negative-int}> $frames
     *
     * @return list<string>
     */
    private function messagesOf(array $frames): array
    {
        $messages = [];

        foreach ($frames as $frame) {
            foreach ($frame['events'] as $event) {
                $this->assertInstanceOf(PhpunitWarningTriggered::class, $event);

                $messages[] = $event->message();
            }
        }

        return $messages;
    }

    private function warningTriggered(string $message): PhpunitWarningTriggered
    {
        return new PhpunitWarningTriggered(
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
        );
    }
}
