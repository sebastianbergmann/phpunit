<?php
use PHPUnit\Framework\TestCase;

class ThrowExceptionTestCase extends TestCase
{
    public function test()
    {
        throw new RuntimeException('A runtime error occurred');
    }
}
