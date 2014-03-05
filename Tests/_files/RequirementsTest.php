<?php
class RequirementsTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
    }

    /**
     * @requires PHPUnit 1.0
     */
    public function testTwo()
    {
    }

    /**
     * @requires PHP 2.0
     */
    public function testThree()
    {
    }

    /**
     * @requires PHPUnit 2.0
     * @requires PHP 1.0
     */
    public function testFour()
    {
    }

    /**
     * @requires PHP 5.4.0RC6
     */
    public function testFive()
    {
    }

    /**
     * @requires PHP 5.4.0-alpha1
     */
    public function testSix()
    {
    }

    /**
     * @requires PHP 5.4.0beta2
     */
    public function testSeven()
    {
    }

    /**
     * @requires PHP 5.4-dev
     */
    public function testEight()
    {
    }


    /**
     * @requires function testFunc
     */
    public function testNine()
    {
    }

    /**
     * @requires extension testExt
     */
    public function testTen()
    {
    }

    /**
     * @requires PHP 99-dev
     * @requires PHPUnit 9-dev
     * @requires function testFuncOne
     * @requires function testFuncTwo
     * @requires extension testExtOne
     * @requires extension testExtTwo
     */
    public function testAllPossibleRequirements()
    {
    }

    /**
     * @requires function array_merge
     */
    public function testExistingFunction()
    {
    }

    /**
     * @requires extension spl
     */
    public function testExistingExtension()
    {
    }

    /**
     * @requires PHPUnit 1111111
     */
    public function testAlwaysSkip()
    {
    }

    /**
     * @requires PHP 9999999
     */
    public function testAlwaysSkip2()
    {
    }

    /**
     * @requires	  extension	  spl	  
     */
    public function testSpace()
    {
    }
}
