<?php
require_once 'OneTest.php';

class Child1Suite
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Child1');
        $suite->addTestSuite('OneTest');

        return $suite;
    }
}
