<?php

use PHPUnit\Framework\TestCase;

abstract class DependencyTestCase extends TestCase
{
    /**
     * @depends testA
     */
    public function testB($value)
    {
        $this->assertSame(1, $value);
        return 2;
    }
}
