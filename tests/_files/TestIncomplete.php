<?php
use PHPUnit\Framework\TestCase;

class TestIncomplete extends TestCase
{
    protected function runTest()
    {
        $this->markTestIncomplete('Incomplete test');
    }
}
