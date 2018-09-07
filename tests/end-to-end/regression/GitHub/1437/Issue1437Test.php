<?php
use PHPUnit\Framework\TestCase;

class Issue1437Test extends TestCase
{
    public function testFailure()
    {
        ob_start();
        $this->assertTrue(false);
    }
}
