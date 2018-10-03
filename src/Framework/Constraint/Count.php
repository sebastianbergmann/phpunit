<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use Countable;
use Iterator;
use IteratorAggregate;
use Traversable;

class Count extends Constraint
{
    /**
     * @var int
     */
    private $expectedCount;

    /**
     * @var \SplObjectStorage|null
     */
    private $traversableCounts;

    public function __construct(int $expected)
    {
        parent::__construct();

        $this->expectedCount     = $expected;
    }

    public function toString(): string
    {
        return \sprintf(
            'count matches %d',
            $this->expectedCount
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches($other): bool
    {
        return $this->expectedCount === $this->getCountOf($other);
    }

    /**
     * @param iterable $other
     */
    protected function getCountOf($other): ?int
    {
        if ($other instanceof Countable || \is_array($other)) {
            return \count($other);
        }

        if ($other instanceof Traversable) {
            return $this->getCountOfTraversable($other);
        }
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     */
    protected function failureDescription($other): string
    {
        return \sprintf(
            'actual size %d matches expected size %d',
            $this->getCountOf($other),
            $this->expectedCount
        );
    }

    private function getCountOfTraversable(Traversable $traversable): int
    {
        if ($this->traversableCounts === null) {
            $this->traversableCounts = new \SplObjectStorage();
        }

        while ($traversable instanceof IteratorAggregate) {
            $traversable = $traversable->getIterator();
        }

        if ($this->traversableCounts->contains($traversable)) {
            return $this->traversableCounts[$traversable];
        }

        if ($traversable instanceof Iterator) {
            return $this->countIteratorWithoutChangingPositionIfPossible($traversable);
        }

        return $this->getCountOfTraversableThatIsNotIteratorOrIteratorAggregate($traversable);
    }

    private function countIteratorWithoutChangingPositionIfPossible(Iterator $iterator): int
    {
        $key   = $iterator->key();
        $count = $this->getCountOfIterator($iterator);

        $this->traversableCounts->attach($iterator, $count);

        if ($this->iteratorIsRewindable($iterator)) {
            $this->traversableCounts->detach($iterator);
            $this->rewindIterator($iterator, $key);
        }

        return $count;
    }

    private function iteratorIsRewindable(Iterator $iterator): bool
    {
        try {
            $iterator->rewind();
        } catch (\Exception $e) {
            return false;
        }

        return !($iterator instanceof \NoRewindIterator);
    }

    /**
     * Returns the total number of iterations from a iterator.
     * This will fully exhaust the iterator.
     */
    private function getCountOfIterator(Iterator $iterator): int
    {
        for ($countOfGenerator = 0; $iterator->valid(); $iterator->next()) {
            ++$countOfGenerator;
        }

        return $countOfGenerator;
    }

    private function rewindIterator(Iterator $iterator, $key): void
    {
        $iterator->rewind();

        while ($iterator->valid() && $key !== $iterator->key()) {
            $iterator->next();
        }
    }

    private function getCountOfTraversableThatIsNotIteratorOrIteratorAggregate(Traversable $traversable): int
    {
        $count = \iterator_count($traversable);

        $this->traversableCounts->attach($traversable, $count);

        return $count;
    }
}
