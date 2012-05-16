<?php
require_once 'Child1Suite.php';
require_once 'Child2Suite.php';

class ParentSuite
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Parent');
        $suite->addTestSuite(Child1Suite::suite());
        $suite->addTestSuite(Child2Suite::suite());

        return $suite;
    }
}
