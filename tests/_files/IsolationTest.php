<?php
use PHPUnit\Framework\TestCase;

class IsolationTest extends TestCase
{
    public function testIsInIsolationReturnsFalse()
    {
        $this->assertFalse($this->isInIsolation());
    }

    public function testIsInIsolationReturnsTrue()
    {
        $this->assertTrue($this->isInIsolation());
    }
}
