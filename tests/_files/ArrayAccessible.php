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

use function array_key_exists;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use ReturnTypeWillChange;

class ArrayAccessible implements ArrayAccess, IteratorAggregate
{
    private $array;

    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->array);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->array[$offset]);
    }

    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->array);
    }
}
