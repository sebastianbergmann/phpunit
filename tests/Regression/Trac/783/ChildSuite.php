<?php
use PHPUnit\Framework\TestSuite;

require_once 'OneTest.php';
require_once 'TwoTest.php';

class ChildSuite
{
    public static function suite()
    {
        $suite = new TestSuite('Child');
        $suite->addTestSuite('OneTest');
        $suite->addTestSuite('TwoTest');

        return $suite;
    }
}
