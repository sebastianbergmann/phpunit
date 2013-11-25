<?php

require_once 'SomeStack.php';

/**
 * Class Issue1075test
 * @dependsInjectionPolicy CLONE
 * @runInSeparateProcess
 *
 */
class Issue1075CloneTest extends PHPUnit_Framework_TestCase
{

    public function testStackInitiallyEmpty()
    {
        $stack = new CloneStack();

        $this->assertEmpty($stack->getItems());
        $this->assertEquals(1, $stack::$ctr);

        return $stack;
    }

    /**
     * @depends testStackInitiallyEmpty
     */
    public function testAddItem($stack)
    {
        $stack->addItem('SomeItem');

        $this->assertNotEmpty($stack->getItems());
        $this->assertEquals(1, $stack::$ctr);
    }

    /**
     * @depends testStackInitiallyEmpty
     */
    public function testSomethingElseThatWantsAnEmptyStack($stack)
    {
        $this->assertEmpty($stack->getItems());
        $this->assertEquals(1, $stack::$ctr);
    }
}
