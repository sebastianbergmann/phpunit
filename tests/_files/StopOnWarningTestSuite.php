<?php
use PHPUnit\Framework\TestSuite;

class StopOnWarningTestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('Test Warnings');

        $suite->addTestSuite(NoTestCases::class);
        $suite->addTestSuite(CoverageClassTest::class);

        return $suite;
    }
}
