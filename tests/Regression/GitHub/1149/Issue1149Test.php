<?php
use PHPUnit\Framework\TestCase;

class Issue1149Test extends TestCase
{
    public function testOne()
    {
        $this->assertTrue(true);
        print '1';
    }

    /**
     * @runInSeparateProcess
     */
    public function testTwo()
    {
        $this->assertTrue(true);
        print '2';
    }
}
