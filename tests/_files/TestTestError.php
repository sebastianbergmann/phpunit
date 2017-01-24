<?php
use PHPUnit\Framework\TestCase;

class TestError extends TestCase
{
    protected function runTest()
    {
        throw new Exception;
    }
}
