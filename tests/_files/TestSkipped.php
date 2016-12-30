<?php
use PHPUnit\Framework\TestCase;

class TestSkipped extends TestCase
{
    protected function runTest()
    {
        $this->markTestSkipped('Skipped test');
    }
}
