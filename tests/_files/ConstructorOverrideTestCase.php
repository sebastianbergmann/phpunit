<?php
use PHPUnit\Framework\TestCase;

class ConstructorOverrideTestCase extends TestCase
{
    public function __construct()
    {
    }

    public function testOne()
    {
        $this->assertFalse(true);
    }
}
