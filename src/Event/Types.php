<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

final class Types implements IteratorAggregate
{
    private array $types;

    public function __construct(Type ...$types)
    {
        $this->types = $types;
    }

    /**
     * @return Iterator<Type>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->types);
    }

    public function contains(Type $other): bool
    {
        foreach ($this->types as $type) {
            if ($type->is($other)) {
                return true;
            }
        }

        return false;
    }
}
