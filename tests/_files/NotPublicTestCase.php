<?php
use PHPUnit\Framework\TestCase;

class NotPublicTestCase extends TestCase
{
    public function testPublic()
    {
    }

    protected function testNotPublic()
    {
    }
}
