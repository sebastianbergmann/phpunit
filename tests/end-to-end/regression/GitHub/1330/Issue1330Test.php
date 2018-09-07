<?php
use PHPUnit\Framework\TestCase;

class Issue1330Test extends TestCase
{
    public function testTrue()
    {
        $this->assertTrue(PHPUNIT_1330);
    }
}
