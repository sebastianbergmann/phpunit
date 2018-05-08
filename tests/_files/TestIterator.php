<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TestIterator implements Iterator
{
    protected $array;
    protected $position = 0;

    public function __construct($array = [])
    {
        $this->array = $array;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid()
    {
        return $this->position < \count($this->array);
    }

    public function key()
    {
        return $this->position;
    }

    public function current()
    {
        return $this->array[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
