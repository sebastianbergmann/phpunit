<?php
use PHPUnit\Framework\TestCase;

class AssertionExampleTest extends TestCase
{
    public function testOne()
    {
        $e = new AssertionExample;

        $e->doSomething();
    }
}
