<?php
use PHPUnit\Framework\TestSuite;

class DependencyTestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('Test Dependencies');

        $suite->addTestSuite(DependencySuccessTest::class);
        $suite->addTestSuite(DependencyFailureTest::class);

        return $suite;
    }
}
