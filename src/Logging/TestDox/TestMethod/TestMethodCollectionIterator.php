<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function count;
use Iterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestMethodCollectionIterator implements Iterator
{
    /**
     * @psalm-var list<TestMethod>
     */
    private readonly array $testMethods;
    private int $position = 0;

    public function __construct(TestMethodCollection $testMethods)
    {
        $this->testMethods = $testMethods->asArray();
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < count($this->testMethods);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): TestMethod
    {
        return $this->testMethods[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
