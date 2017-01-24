<?php
use PHPUnit\Framework\TestCase;

class CoverageNamespacedFunctionTest extends TestCase
{
    /**
     * @covers foo\func()
     */
    public function testFunc()
    {
        foo\func();
    }
}
