<?php
use PHPUnit\Framework\TestCase;

class Issue765Test extends TestCase
{
    public function testDependee()
    {
        $this->assertTrue(true);
    }

    /**
     * @depends testDependee
     * @dataProvider dependentProvider
     */
    public function testDependent($a)
    {
        $this->assertTrue(true);
    }

    public function dependentProvider()
    {
        throw new Exception;
    }
}
