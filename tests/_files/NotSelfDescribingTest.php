<?php

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;

class NotSelfDescribingTest implements Test
{
    public function log($msg): void
    {
        print $msg;
    }

    public function count(): int
    {
        return 0;
    }

    public function run(TestResult $result = null): TestResult
    {
        return new TestResult();
    }
}
