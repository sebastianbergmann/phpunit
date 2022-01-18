<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use function count;
use Iterator;
use PHPUnit\Event\Test\Failure;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class FailureCollectionIterator implements Iterator
{
    /**
     * @psalm-var list<Failure>
     */
    private array $failures;
    private int $position = 0;

    public function __construct(FailureCollection $failures)
    {
        $this->failures = $failures->asArray();
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < count($this->failures);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): Failure
    {
        return $this->failures[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
