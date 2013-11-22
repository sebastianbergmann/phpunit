<?php

class SomeStack
{
    private $items = array();

    public function addItem($item)
    {
        $this->items[] = $item;
    }

    public function getItems()
    {
        return $this->items;
    }
}

class Issue1075test extends PHPUnit_Framework_TestCase
{
    public function testStackInitiallyEmpty()
    {
        $stack = new SomeStack();

        $this->assertEmpty($stack->getItems());

        return $stack;
    }

    /**
     * @depends testStackInitiallyEmpty
     */
    public function testAddItem($stack)
    {
        $stack->addItem('SomeItem');

        $this->assertNotEmpty($stack->getItems());
    }

    /**
     * @depends testStackInitiallyEmpty
     */
    public function testSomethingElseThatWantsAnEmptyStack($stack)
    {
        $this->assertEmpty($stack->getItems()); // Fails
    }
}
