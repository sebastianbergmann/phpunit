<?php
use PHPUnit\Framework\TestSuite;

class StopOnWarningTestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('Test Warnings');

        $suite->addTestSuite('NoTestCases');
        $suite->addTestSuite('CoverageClassTest');

        return $suite;
    }
}
