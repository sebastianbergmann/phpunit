<?php

class ConfigurationTestClass implements PHPUnit_Util_Configuration_Interface {

    /**
     * Returns the realpath to the configuration file.
     *
     * @return string
     * @since  Method available since Release 3.6.0
     */
    public function getFilename()
    {
    }

    /**
     * Returns the configuration for SUT filtering.
     *
     * @return array
     * @since  Method available since Release 3.2.1
     */
    public function getFilterConfiguration()
    {
    }

    /**
     * Returns the configuration for groups.
     *
     * @return array
     * @since  Method available since Release 3.2.1
     */
    public function getGroupConfiguration()
    {
    }

    /**
     * Returns the configuration for listeners.
     *
     * @return array
     * @since  Method available since Release 3.4.0
     */
    public function getListenerConfiguration()
    {
    }

    /**
     * Returns the logging configuration.
     *
     * @return array
     */
    public function getLoggingConfiguration()
    {
    }

    /**
     * Returns the PHP configuration.
     *
     * @return array
     * @since  Method available since Release 3.2.1
     */
    public function getPHPConfiguration()
    {
    }

    /**
     * Handles the PHP configuration.
     *
     * @since  Method available since Release 3.2.20
     */
    public function handlePHPConfiguration()
    {
    }

    /**
     * Returns the PHPUnit configuration.
     *
     * @return array
     * @since  Method available since Release 3.2.14
     */
    public function getPHPUnitConfiguration()
    {
        return array(
            'configurationTestValue' => TRUE
        );
    }

    /**
     * Returns the test suite configuration.
     *
     * @return PHPUnit_Framework_TestSuite
     * @since  Method available since Release 3.2.1
     */
    public function getTestSuiteConfiguration($testSuiteFilter = null)
    {
    }
}
