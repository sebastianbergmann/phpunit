<?php

use PHPUnit\Framework\TestCase;

class DummyFooTest extends TestCase
{
    public function testFooEqualsFoo()
    {
        $this->assertEquals('Foo', 'Foo');
    }
}
