<?php
use PHPUnit\Framework\TestSuite;

class DependencyTestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('Test Dependencies');

        $suite->addTestSuite('DependencySuccessTest');
        $suite->addTestSuite('DependencyFailureTest');

        return $suite;
    }
}
