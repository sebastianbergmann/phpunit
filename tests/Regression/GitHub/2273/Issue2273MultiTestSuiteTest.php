<?php

class Issue2273MultiTestSuiteTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['argv']    = [];
        $_SERVER['argv'][1] = '--configuration';
        $_SERVER['argv'][2] = __DIR__ . '/testData/phpunit.xml';
        $_SERVER['argv'][3] = '--testsuite=unit|integration';
    }

    /**
     * @test
     */
    public function iShouldRunAllTestsFromAllRequiredSuites()
    {
        PHPUnit_TextUI_Command::main(true);
    }

}
