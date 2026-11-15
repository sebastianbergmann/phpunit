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

use function is_int;
use function pack;
use function serialize;
use function strlen;
use function substr;
use function unpack;
use function unserialize;
use PHPUnit\Event\EventCollection;
use PHPUnit\Framework\TestRunner\ChildProcessResultEnvelope;

/**
 * The wire format with which a worker streams events to the parent process
 * while a unit of work is still running.
 *
 * The stream is a sequence of frames that the worker appends to a file. Each
 * frame carries the events collected since the previous frame, serialized and
 * prefixed with the nonce that authenticates them and with the number of
 * bytes that the worker had written to its standard output and its standard
 * error when it wrote the frame; the whole payload is in turn prefixed with
 * its length. Both numbers are unsigned 64-bit big-endian integers:
 *
 *     length (8 bytes) | nonce | output length (8 bytes) | serialized EventCollection
 *
 * The output length is what lets the parent process tell what the worker
 * wrote before the tests of a frame finished from what it wrote after: the
 * output of the worker is redirected to a file that the parent process reads
 * (see PersistentWorker).
 *
 * The parent reads the file incrementally while the worker is still appending
 * to it, without any locking. The length prefix is what makes that safe: a
 * frame whose bytes are not yet completely visible to the reader is recognized
 * as such and left for a later read.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class EventStream
{
    private const int FRAME_HEADER_BYTES  = 8;
    private const int OUTPUT_LENGTH_BYTES = 8;

    /**
     * Encode one frame: the given events, serialized and prefixed with the
     * nonce that authenticates them, with the number of bytes that the worker
     * has written to its standard output and its standard error so far, and
     * with the length of the whole payload.
     *
     * The frame for an empty collection of events is the empty string; there
     * is nothing to transport.
     *
     * @param non-empty-string $nonce
     * @param non-negative-int $outputLength
     */
    public static function frame(string $nonce, EventCollection $events, int $outputLength): string
    {
        if ($events->isEmpty()) {
            return '';
        }

        $payload = $nonce . pack('J', $outputLength) . serialize($events);

        return pack('J', strlen($payload)) . $payload;
    }

    /**
     * Decode the complete frames at the beginning of $data into the
     * collections of events they carry, each with the number of bytes that
     * the worker had written to its standard output and its standard error
     * when it wrote the frame.
     *
     * An incomplete frame at the end of $data — one whose bytes are not yet
     * all visible to the reader — is not consumed, so that the caller can
     * present it again once more bytes have arrived.
     *
     * A frame whose nonce does not match or whose payload does not decode to
     * a collection of events taints the stream: it was tampered with or
     * written by an unexpected process, and reading stops at the offending
     * frame. The frames decoded before it are still returned, because the
     * caller may already have forwarded the frames of earlier reads anyway;
     * deciding what a tainted stream means for the unit is the caller's
     * responsibility.
     *
     * @param ?non-empty-string $nonce
     *
     * @return array{frames: list<array{events: EventCollection, outputLength: non-negative-int}>, bytesConsumed: non-negative-int, tainted: bool}
     */
    public static function readFrames(string $data, ?string $nonce): array
    {
        $frames    = [];
        $available = strlen($data);
        $position  = 0;

        while ($available - $position >= self::FRAME_HEADER_BYTES) {
            $header = unpack('Jlength', substr($data, $position, self::FRAME_HEADER_BYTES));

            // unpack() cannot fail on exactly eight bytes; the first two checks
            // merely keep the reader honest should it ever return something
            // unexpected. A length that unpack() reports as negative overflowed
            // the signed 64-bit range and cannot be the length of a frame that
            // was ever written.
            if ($header === false || !isset($header['length']) || !is_int($header['length']) || $header['length'] < 0) {
                return self::tainted($frames, $position);
            }

            $length = $header['length'];

            if ($available - $position - self::FRAME_HEADER_BYTES < $length) {
                break;
            }

            $payload = substr($data, $position + self::FRAME_HEADER_BYTES, $length);

            $verifiedPayload = ChildProcessResultEnvelope::verifyAndStripNonce($payload, $nonce);

            if ($verifiedPayload === null || strlen($verifiedPayload) < self::OUTPUT_LENGTH_BYTES) {
                return self::tainted($frames, $position);
            }

            $outputHeader = unpack('Jlength', substr($verifiedPayload, 0, self::OUTPUT_LENGTH_BYTES));

            // see above: unpack() cannot fail on exactly eight bytes
            if ($outputHeader === false || !isset($outputHeader['length']) || !is_int($outputHeader['length']) || $outputHeader['length'] < 0) {
                return self::tainted($frames, $position);
            }

            $events = @unserialize(substr($verifiedPayload, self::OUTPUT_LENGTH_BYTES));

            if (!$events instanceof EventCollection) {
                return self::tainted($frames, $position);
            }

            $frames[] = [
                'events'       => $events,
                'outputLength' => $outputHeader['length'],
            ];

            $position += self::FRAME_HEADER_BYTES + $length;
        }

        return [
            'frames'        => $frames,
            'bytesConsumed' => $position,
            'tainted'       => false,
        ];
    }

    /**
     * @param list<array{events: EventCollection, outputLength: non-negative-int}> $frames
     * @param non-negative-int                                                     $bytesConsumed
     *
     * @return array{frames: list<array{events: EventCollection, outputLength: non-negative-int}>, bytesConsumed: non-negative-int, tainted: true}
     */
    private static function tainted(array $frames, int $bytesConsumed): array
    {
        return [
            'frames'        => $frames,
            'bytesConsumed' => $bytesConsumed,
            'tainted'       => true,
        ];
    }
}
