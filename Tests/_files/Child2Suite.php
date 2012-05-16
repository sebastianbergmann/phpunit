<?php
require_once 'OneTest.php';

class Child2Suite
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Child2');
        $suite->addTestSuite('OneTest');

        return $suite;
    }
}
