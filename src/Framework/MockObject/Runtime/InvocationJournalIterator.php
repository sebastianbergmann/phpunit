<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function assert;
use Iterator;

/**
 * @template-implements Iterator<non-negative-int, non-empty-string>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvocationJournalIterator implements Iterator
{
    /**
     * @var list<non-empty-string>
     */
    private readonly array $invocations;

    /**
     * @var non-negative-int
     */
    private int $position = 0;

    public function __construct(InvocationJournal $journal)
    {
        $this->invocations = $journal->asArray();
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->invocations[$this->position]);
    }

    /**
     * @return non-negative-int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @return non-empty-string
     */
    public function current(): string
    {
        assert(isset($this->invocations[$this->position]));

        return $this->invocations[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
