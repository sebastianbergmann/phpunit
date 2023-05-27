<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function count;
use Iterator;

final class TestIterator implements Iterator
{
    private array $array;
    private int $position = 0;

    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < count($this->array);
    }

    public function key(): int|string
    {
        return $this->position;
    }

    public function current(): mixed
    {
        return $this->array[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
