<?php
use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;

class Issue2758TestListener extends BaseTestListener
{
    public function endTest(Test $test, $time)
    {
        if (!$test instanceof TestCase) {
            return;
        }

        $test->addToAssertionCount(1);
    }
}
