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
use Generator;
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
     * @var \SplObjectStorage
     */
    private $iteratorCounts;

    public function __construct(int $expected)
    {
        parent::__construct();

        $this->expectedCount     = $expected;
        $this->iteratorCounts    = new \SplObjectStorage();
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
        while ($traversable instanceof IteratorAggregate) {
            $traversable = $traversable->getIterator();
        }

        if ($traversable instanceof Generator) {
            return $this->getCountOfNonRewindableIterator($traversable);
        }

        if ($traversable instanceof Iterator) {
            return $this->getCountOfRewindableIterator($traversable);
        }

        return \iterator_count($traversable);
    }

    /**
     * Returns the total number of iterations from a iterator.
     * This will fully exhaust the generator.
     */
    private function getCountOfNonRewindableIterator(Iterator $iterator): int
    {
        if (!$this->iteratorCounts->contains($iterator)) {
            for ($countOfGenerator = 0; $iterator->valid(); $iterator->next()) {
                ++$countOfGenerator;
            }

            $this->iteratorCounts->attach($iterator, $countOfGenerator);
        }

        return $this->iteratorCounts[$iterator];
    }

    private function getCountOfRewindableIterator(Iterator $iterator): int
    {
        $key   = $iterator->key();
        $count = \iterator_count($iterator);

        // Manually rewind $iterator to previous key, since iterator_count
        // moves pointer.
        if ($key !== null) {
            $iterator->rewind();

            while ($iterator->valid() && $key !== $iterator->key()) {
                $iterator->next();
            }
        }

        return $count;
    }
}
