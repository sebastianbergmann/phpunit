<?php

class WrapperIteratorAggregate implements IteratorAggregate
{
    /**
     * @var array|\Traversable
     */
    private $baseCollection;

    public function __construct($baseCollection)
    {
        assert(is_array($baseCollection) || $baseCollection instanceof Traversable);
        $this->baseCollection = $baseCollection;
    }

    public function getIterator()
    {
        foreach ($this->baseCollection as $k => $v) {
            yield $k => $v;
        }
    }
}
