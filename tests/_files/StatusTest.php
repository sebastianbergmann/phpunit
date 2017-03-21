<?php
namespace vendor\project;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;

class StatusTest extends TestCase
{
    public function testSuccess()
    {
        $this->assertTrue(true);
    }

    public function testFailure()
    {
        $this->assertTrue(false);
    }

    public function testError()
    {
        throw new \RuntimeException;
    }

    public function testIncomplete()
    {
        $this->markTestIncomplete();
    }

    public function testSkipped()
    {
        $this->markTestSkipped();
    }

    public function testRisky()
    {
    }

    public function testWarning()
    {
        throw new Warning;
    }
}
