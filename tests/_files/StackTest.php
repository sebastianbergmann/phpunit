<?php
use PHPUnit\Framework\TestCase;

class StackTest extends TestCase
{
    public function testPush()
    {
        $stack = [];
        $this->assertCount(0, $stack);

        $stack[] = 'foo';
        $this->assertEquals('foo', end($stack));
        $this->assertCount(1, $stack);

        return $stack;
    }

    /**
     * @depends testPush
     */
    public function testPop(array $stack)
    {
        $this->assertEquals('foo', array_pop($stack));
        $this->assertCount(0, $stack);
    }
}
