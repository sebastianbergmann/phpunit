<?php
require 'CoveredFunction.php';

class CoverageTest extends PHPUnit_Framework_TestCase
{
    public function testFunction()
    {
        global_function();
    }
}
