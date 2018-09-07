<?php
use PHPUnit\Framework\TestCase;

class Issue1471Test extends TestCase
{
    public function testFailure()
    {
        $this->expectOutputString('*');

        print '*';

        $this->assertTrue(false);
    }
}
