<?php

/* This class is used for testing a chain of IteratorAggregate objects, since
 * PHP does allow IteratorAggregate::getIterator() to return an instance of the
 * same class. */
class TestIteratorAggregate2 implements IteratorAggregate
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
