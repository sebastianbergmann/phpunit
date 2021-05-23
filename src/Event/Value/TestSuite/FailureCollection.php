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

use Countable;
use IteratorAggregate;
use PHPUnit\Event\Test\Failure;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class FailureCollection implements Countable, IteratorAggregate
{
    /**
     * @psalm-var list<Failure>
     */
    private array $failures;

    public function __construct(Failure ...$failures)
    {
        $this->failures = $failures;
    }

    /**
     * @psalm-return list<Failure>
     */
    public function asArray(): array
    {
        return $this->failures;
    }

    public function count(): int
    {
        return count($this->failures);
    }

    public function getIterator(): FailureCollectionIterator
    {
        return new FailureCollectionIterator($this);
    }
}
