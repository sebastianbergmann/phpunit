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

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PHPUnit\Event\Test\Failure;

final class FailureCollection implements Countable, IteratorAggregate
{
    /**
     * @psalm-var list<Failure>
     *
     * @var array<int, Failure>
     */
    private array $failures;

    public function __construct(Failure ...$failures)
    {
        $this->failures = $failures;
    }

    public function count(): int
    {
        return count($this->failures);
    }

    /**
     * @return ArrayIterator<int, Failure>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->failures);
    }
}
