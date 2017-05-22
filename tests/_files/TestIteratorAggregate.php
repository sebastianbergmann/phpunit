<?php

class TestIteratorAggregate implements IteratorAggregate
{
    private $traversable;

    public function __construct(\Traversable $traversable)
    {
        $this->traversable = $traversable;
    }

    public function getIterator()
    {
        return $this->traversable;
    }
}
