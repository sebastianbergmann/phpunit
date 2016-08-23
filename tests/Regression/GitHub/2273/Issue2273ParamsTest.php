<?php

class Issue2273ParamsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['argv'][1] = '--configuration';
        $_SERVER['argv'][2] = __DIR__ . '/testData/phpunit.xml';
        $_SERVER['argv'][3] = '--testsuite=unit|integration';
    }

    /**
     * @test
     */
    public function iShouldBeAbleToRetrieveValidScriptParams()
    {
        $expectedParam = '--testsuite=unit|integration';
        $strictCompare = true;
        $this->assertTrue(in_array($expectedParam, $_SERVER['argv'], $strictCompare));

        $configFilePath = $_SERVER['argv'][2];
        $this->assertFileExists($configFilePath);
    }

    /**
     * @test
     */
    public function configFileShouldContainValidTestSuites()
    {
        $expectedSuites = ['unit', 'integration'];
        $configuration = $this->getUtilConfiguration();
        $this->assertSame($expectedSuites, $configuration->getTestSuiteNames());
    }

    private function getUtilConfiguration()
    {
        $configFilePath = $_SERVER['argv'][2];

        return PHPUnit_Util_Configuration::getInstance($configFilePath);
    }
}
