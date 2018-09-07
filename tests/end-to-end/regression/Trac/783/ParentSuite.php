<?php
use PHPUnit\Framework\TestSuite;

require_once 'ChildSuite.php';

class ParentSuite
{
    public static function suite()
    {
        $suite = new TestSuite('Parent');
        $suite->addTest(ChildSuite::suite());

        return $suite;
    }
}
