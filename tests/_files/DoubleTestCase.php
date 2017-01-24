<?php
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Test;

class DoubleTestCase implements Test
{
    protected $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function count()
    {
        return 2;
    }

    public function run(TestResult $result = null)
    {
        $result->startTest($this);

        $this->testCase->runBare();
        $this->testCase->runBare();

        $result->endTest($this, 0);
    }
}
