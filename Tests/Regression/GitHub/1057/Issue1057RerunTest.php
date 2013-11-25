<?php

require_once 'SomeStack.php';

/**
 * Class Issue1075test
 * @dependsInjectionPolicy RERUN
 * @runInSeparateProcess
 */
class Issue1057RerunTest extends PHPUnit_Framework_TestCase
{
    public function testStackInitiallyEmpty()
    {
        $stack = new RerunStack();

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
        $this->assertEquals(2, $stack::$ctr);

        return $stack;
    }

    /**
     * @depends testStackInitiallyEmpty
     */
    public function testSomethingElseThatWantsAnEmptyStack($stack)
    {
        $this->assertEmpty($stack->getItems());
        $this->assertEquals(3, $stack::$ctr);

        return $stack;
    }

    /**
     * @depends testAddItem
     */
    public function testMultiLevelDepends($stack)
    {
        $this->assertNotEmpty($stack->getItems());
    }
}
