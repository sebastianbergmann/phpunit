<?php
use PHPUnit\Framework\TestCase;

class Issue1021Test extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testSomething($data)
    {
        $this->assertTrue($data);
    }

    /**
     * @depends testSomething
     */
    public function testSomethingElse()
    {
        $this->assertTrue(true);
    }

    public function provider()
    {
        return [[true]];
    }
}
