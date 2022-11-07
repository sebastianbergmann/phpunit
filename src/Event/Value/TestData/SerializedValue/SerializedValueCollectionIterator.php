<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestData;

use function count;
use Iterator;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class SerializedValueCollectionIterator implements Iterator
{
    /**
     * @psalm-var list<SerializedValue>
     */
    private readonly array $values;
    private int $position = 0;

    public function __construct(SerializedValueCollection $values)
    {
        $this->values = $values->asArray();
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < count($this->values);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): SerializedValue
    {
        return $this->values[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
