<?php

use PHPUnit\Framework\TestCase;

class DummyBarTest extends TestCase
{
    public function testBarEqualsBar()
    {
        $this->assertEquals('Bar', 'Bar');
    }
}
