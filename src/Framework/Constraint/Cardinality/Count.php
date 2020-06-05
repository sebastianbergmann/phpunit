<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\Exception;

class Count extends Constraint
{
    /**
     * @var int
     */
    private $expectedCount;

    public function __construct(int $expected)
    {
        $this->expectedCount = $expected;
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
     *
     * @throws Exception
     */
    protected function matches($other): bool
    {
        return $this->expectedCount === $this->getCountOf($other);
    }

    /**
     * @throws Exception
     */
    protected function getCountOf($other): ?int
    {
        if ($other instanceof \Countable || \is_array($other)) {
            return \count($other);
        }

        if ($other instanceof \EmptyIterator) {
            return 0;
        }

        if ($other instanceof \Traversable) {
            while ($other instanceof \IteratorAggregate) {
                try {
                    $other = $other->getIterator();
                } catch (\Exception $e) {
                    throw new Exception(
                        $e->getMessage(),
                        $e->getCode(),
                        $e
                    );
                }
            }

            $iterator = $other;

            if ($iterator instanceof \Generator) {
                return $this->getCountOfGenerator($iterator);
            }

            if (!$iterator instanceof \Iterator) {
                return \iterator_count($iterator);
            }

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

        return null;
    }

    /**
     * Returns the total number of iterations from a generator.
     * This will fully exhaust the generator.
     */
    protected function getCountOfGenerator(\Generator $generator): int
    {
        for ($count = 0; $generator->valid(); $generator->next()) {
            ++$count;
        }

        return $count;
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
            (int) $this->getCountOf($other),
            $this->expectedCount
        );
    }
}
