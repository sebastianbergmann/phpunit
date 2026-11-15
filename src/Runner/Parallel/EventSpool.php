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

use function assert;
use function count;
use function fread;
use function fseek;
use function ftruncate;
use function fwrite;
use function is_array;
use function is_string;
use function serialize;
use function strlen;
use function tmpfile;
use function unserialize;
use Generator;
use PHPUnit\Event\EventCollection;

/**
 * Keeps the events that units streamed before it is their turn in the release
 * sequence, and what their workers wrote to their standard output and to
 * their standard error before they streamed them, in a temporary file rather
 * than in memory.
 *
 * The events of a unit are held back until every unit that precedes it in
 * suite order has been released. A single slow test class can therefore hold
 * back the events of most of a test suite, and the events of a test are big:
 * each of them carries the value object of the test and telemetry information.
 * Held back as objects, the events of a few thousand tests need more memory
 * than a PHP process is commonly allowed to use.
 *
 * The worker that streamed the events already wrote them to a file once. They
 * are written to a file again here, and read back, collection by collection,
 * when they are forwarded: what is held in memory for a unit that waits for
 * its turn is where its events are in that file.
 *
 * The file is created when the first events are held back, and it is deleted
 * by PHP when it is closed or when the process ends. It is emptied whenever no
 * events are held back anymore, so that it does not grow with the number of
 * events that were held back during the test run, only with the number that
 * are held back at the same time.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class EventSpool
{
    /**
     * @var ?resource
     */
    private $file;

    /**
     * @var non-negative-int
     */
    private int $size = 0;

    /**
     * Where the collections of events of each unit are in the file, in the
     * order in which they were added: their offset and their length.
     *
     * @var array<non-negative-int, list<array{0: non-negative-int, 1: positive-int}>>
     */
    private array $collections = [];

    /**
     * @param non-negative-int $index
     *
     * @throws WorkerException
     */
    public function add(int $index, EventCollection $events, string $output, string $errorOutput): void
    {
        $serializedEvents = serialize([$events, $output, $errorOutput]);
        $length           = strlen($serializedEvents);

        assert($length > 0);

        $file = $this->file();

        if (fseek($file, $this->size) !== 0 || fwrite($file, $serializedEvents) !== $length) {
            // @codeCoverageIgnoreStart
            throw new WorkerException('Unable to write the events of a unit that waits for its turn to a temporary file');
            // @codeCoverageIgnoreEnd
        }

        if (!isset($this->collections[$index])) {
            $this->collections[$index] = [];
        }

        $this->collections[$index][] = [$this->size, $length];

        $this->size += $length;
    }

    /**
     * @param non-negative-int $index
     */
    public function has(int $index): bool
    {
        return isset($this->collections[$index]);
    }

    /**
     * The collections of events that were added for the unit, each with the
     * output and the error output that were added with it, in the order in
     * which they were added.
     * They are no longer kept: a collection is read from the file when it is
     * asked for, so that only one of them is in memory at a time.
     *
     * @param non-negative-int $index
     *
     * @throws WorkerException
     *
     * @return Generator<int, array{0: EventCollection, 1: string, 2: string}>
     */
    public function take(int $index): Generator
    {
        $collections = [];

        if (isset($this->collections[$index])) {
            $collections = $this->collections[$index];
        }

        unset($this->collections[$index]);

        foreach ($collections as [$offset, $length]) {
            yield $this->read($offset, $length);
        }

        $this->emptyFileWhenNothingIsKept();
    }

    /**
     * @param non-negative-int $index
     */
    public function discard(int $index): void
    {
        unset($this->collections[$index]);

        $this->emptyFileWhenNothingIsKept();
    }

    /**
     * @param non-negative-int $offset
     * @param positive-int     $length
     *
     * @throws WorkerException
     *
     * @return array{0: EventCollection, 1: string, 2: string}
     */
    private function read(int $offset, int $length): array
    {
        $file = $this->file();

        $serializedEvents = false;

        if (fseek($file, $offset) === 0) {
            $serializedEvents = fread($file, $length);
        }

        if ($serializedEvents === false || strlen($serializedEvents) !== $length) {
            // @codeCoverageIgnoreStart
            throw new WorkerException('Unable to read the events of a unit that waited for its turn from a temporary file');
            // @codeCoverageIgnoreEnd
        }

        $eventsAndOutput = unserialize($serializedEvents);

        assert(is_array($eventsAndOutput));
        assert(isset($eventsAndOutput[0], $eventsAndOutput[1], $eventsAndOutput[2]));
        assert($eventsAndOutput[0] instanceof EventCollection);
        assert(is_string($eventsAndOutput[1]));
        assert(is_string($eventsAndOutput[2]));

        return [$eventsAndOutput[0], $eventsAndOutput[1], $eventsAndOutput[2]];
    }

    private function emptyFileWhenNothingIsKept(): void
    {
        if ($this->file === null || $this->size === 0 || count($this->collections) > 0) {
            return;
        }

        ftruncate($this->file, 0);

        $this->size = 0;
    }

    /**
     * @throws WorkerException
     *
     * @return resource
     */
    private function file()
    {
        if ($this->file !== null) {
            return $this->file;
        }

        $file = tmpfile();

        if ($file === false) {
            // @codeCoverageIgnoreStart
            throw new WorkerException('Unable to create a temporary file for the events of units that wait for their turn');
            // @codeCoverageIgnoreEnd
        }

        $this->file = $file;

        return $this->file;
    }
}
