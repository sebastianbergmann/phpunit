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
 * prefixed with the nonce that authenticates them; the whole payload is in
 * turn prefixed with its length as an unsigned 64-bit big-endian integer:
 *
 *     length (8 bytes) | nonce | serialized EventCollection
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
    private const int FRAME_HEADER_BYTES = 8;

    /**
     * Encode one frame: the given events, serialized and prefixed with the
     * nonce that authenticates them and with the length of the whole payload.
     *
     * The frame for an empty collection of events is the empty string; there
     * is nothing to transport.
     *
     * @param non-empty-string $nonce
     */
    public static function frame(string $nonce, EventCollection $events): string
    {
        if ($events->isEmpty()) {
            return '';
        }

        $payload = $nonce . serialize($events);

        return pack('J', strlen($payload)) . $payload;
    }

    /**
     * Decode the complete frames at the beginning of $data into the
     * collections of events they carry.
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
     * @return array{frames: list<EventCollection>, bytesConsumed: non-negative-int, tainted: bool}
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

            $serializedEvents = ChildProcessResultEnvelope::verifyAndStripNonce($payload, $nonce);

            if ($serializedEvents === null) {
                return self::tainted($frames, $position);
            }

            $events = @unserialize($serializedEvents);

            if (!$events instanceof EventCollection) {
                return self::tainted($frames, $position);
            }

            $frames[] = $events;

            $position += self::FRAME_HEADER_BYTES + $length;
        }

        return [
            'frames'        => $frames,
            'bytesConsumed' => $position,
            'tainted'       => false,
        ];
    }

    /**
     * @param list<EventCollection> $frames
     * @param non-negative-int      $bytesConsumed
     *
     * @return array{frames: list<EventCollection>, bytesConsumed: non-negative-int, tainted: true}
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
