<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.0
 */

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.0
 */
class Util_ConfigurationTest extends PHPUnit_Framework_TestCase
{
    protected $configuration;

    protected function setUp()
    {
        $this->configuration = PHPUnit_Util_Configuration::getInstance(
          dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration.xml'
        );
    }

    /**
     * @covers PHPUnit_Util_Configuration::getInstance
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testExceptionIsThrownForNotExistingConfigurationFile()
    {
        PHPUnit_Util_Configuration::getInstance('not_existing_file.xml');
    }

    /**
     * @covers PHPUnit_Util_Configuration::getFilterConfiguration
     */
    public function testFilterConfigurationIsReadCorrectly()
    {
        $this->assertEquals(
          array(
            'blacklist' =>
            array(
              'include' =>
              array(
                'directory' =>
                array(
                  0 =>
                  array(
                    'path' => '/path/to/files',
                    'prefix' => '',
                    'suffix' => '.php',
                    'group' => 'DEFAULT'
                  ),
                ),
                'file' =>
                array(
                  0 => '/path/to/file',
                ),
              ),
              'exclude' =>
              array(
                'directory' =>
                array(
                  0 =>
                  array(
                    'path' => '/path/to/files',
                    'prefix' => '',
                    'suffix' => '.php',
                    'group' => 'DEFAULT'
                  ),
                ),
                'file' =>
                array(
                  0 => '/path/to/file',
                ),
              ),
            ),
            'whitelist' =>
            array(
              'addUncoveredFilesFromWhitelist' => true,
              'processUncoveredFilesFromWhitelist' => false,
              'include' =>
              array(
                'directory' =>
                array(
                  0 =>
                  array(
                    'path' => '/path/to/files',
                    'prefix' => '',
                    'suffix' => '.php',
                    'group' => 'DEFAULT'
                  ),
                ),
                'file' =>
                array(
                  0 => '/path/to/file',
                ),
              ),
              'exclude' =>
              array(
                'directory' =>
                array(
                  0 =>
                  array(
                    'path' => '/path/to/files',
                    'prefix' => '',
                    'suffix' => '.php',
                    'group' => 'DEFAULT'
                  ),
                ),
                'file' =>
                array(
                  0 => '/path/to/file',
                ),
              ),
            ),
          ),
          $this->configuration->getFilterConfiguration()
        );
    }

    /**
     * @covers PHPUnit_Util_Configuration::getGroupConfiguration
     */
    public function testGroupConfigurationIsReadCorrectly()
    {
        $this->assertEquals(
          array(
            'include' =>
            array(
              0 => 'name',
            ),
            'exclude' =>
            array(
              0 => 'name',
            ),
          ),
          $this->configuration->getGroupConfiguration()
        );
    }

    /**
     * @covers PHPUnit_Util_Configuration::getListenerConfiguration
     */
    public function testListenerConfigurationIsReadCorrectly()
    {
        $dir = __DIR__;
        $includePath = ini_get('include_path');

        ini_set('include_path', $dir . PATH_SEPARATOR . $includePath);

        $this->assertEquals(
          array(
            0 =>
            array(
              'class' => 'MyListener',
              'file' => '/optional/path/to/MyListener.php',
              'arguments' =>
              array(
                0 =>
                array(
                  0 => 'Sebastian',
                ),
                1 => 22,
                2 => 'April',
                3 => 19.78,
                4 => null,
                5 => new stdClass,
                6 => dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'MyTestFile.php',
                7 => dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'MyRelativePath',
              ),
            ),
            array(
              'class' => 'IncludePathListener',
              'file' => __FILE__,
              'arguments' => array()
            ),
            array(
              'class' => 'CompactArgumentsListener',
              'file' => '/CompactArgumentsListener.php',
              'arguments' =>
              array(
                0 => 42
              ),
            ),
          ),
          $this->configuration->getListenerConfiguration()
        );

        ini_set('include_path', $includePath);
    }

    /**
     * @covers PHPUnit_Util_Configuration::getLoggingConfiguration
     */
    public function testLoggingConfigurationIsReadCorrectly()
    {
        $this->assertEquals(
          array(
            'lowUpperBound' => '50',
            'highLowerBound' => '90',
            'coverage-html' => '/tmp/report',
            'coverage-clover' => '/tmp/clover.xml',
            'json' => '/tmp/logfile.json',
            'plain' => '/tmp/logfile.txt',
            'tap' => '/tmp/logfile.tap',
            'logIncompleteSkipped' => false,
            'junit' => '/tmp/logfile.xml',
            'testdox-html' => '/tmp/testdox.html',
            'testdox-text' => '/tmp/testdox.txt',
          ),
          $this->configuration->getLoggingConfiguration()
        );
    }

    /**
     * @covers PHPUnit_Util_Configuration::getPHPConfiguration
     */
    public function testPHPConfigurationIsReadCorrectly()
    {
        $this->assertEquals(
          array(
            'include_path' =>
            array(
              dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . '.',
              '/path/to/lib'
            ),
            'ini'=> array('foo' => 'bar'),
            'const'=> array('FOO' => false, 'BAR' => true),
            'var'=> array('foo' => false),
            'env'=> array('foo' => true),
            'post'=> array('foo' => 'bar'),
            'get'=> array('foo' => 'bar'),
            'cookie'=> array('foo' => 'bar'),
            'server'=> array('foo' => 'bar'),
            'files'=> array('foo' => 'bar'),
            'request'=> array('foo' => 'bar'),
          ),
          $this->configuration->getPHPConfiguration()
        );
    }

    /**
     * @backupGlobals enabled
     * @covers PHPUnit_Util_Configuration::handlePHPConfiguration
     */
    public function testPHPConfigurationIsHandledCorrectly()
    {
        $this->configuration->handlePHPConfiguration();

        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . '.' . PATH_SEPARATOR . '/path/to/lib';
        $this->assertStringStartsWith($path, ini_get('include_path'));
        $this->assertEquals(false, FOO);
        $this->assertEquals(true, BAR);
        $this->assertEquals(false, $GLOBALS['foo']);
        $this->assertEquals(true, $_ENV['foo']);
        $this->assertEquals(true, getenv('foo'));
        $this->assertEquals('bar', $_POST['foo']);
        $this->assertEquals('bar', $_GET['foo']);
        $this->assertEquals('bar', $_COOKIE['foo']);
        $this->assertEquals('bar', $_SERVER['foo']);
        $this->assertEquals('bar', $_FILES['foo']);
        $this->assertEquals('bar', $_REQUEST['foo']);
    }

    /**
     * @backupGlobals enabled
     * @see https://github.com/sebastianbergmann/phpunit/issues/1181
     */
    public function testHandlePHPConfigurationDoesNotOverwrittenExistingEnvArrayVariables()
    {
        $_ENV['foo'] = false;
        $this->configuration->handlePHPConfiguration();

        $this->assertEquals(false, $_ENV['foo']);
        $this->assertEquals(true, getenv('foo'));
    }

    /**
     * @backupGlobals enabled
     * @see https://github.com/sebastianbergmann/phpunit/issues/1181
     */
    public function testHandlePHPConfigurationDoesNotOverriteVariablesFromPutEnv()
    {
        putenv('foo=putenv');
        $this->configuration->handlePHPConfiguration();

        $this->assertEquals(true, $_ENV['foo']);
        $this->assertEquals('putenv', getenv('foo'));
    }

    /**
     * @covers PHPUnit_Util_Configuration::getPHPUnitConfiguration
     */
    public function testPHPUnitConfigurationIsReadCorrectly()
    {
        $this->assertEquals(
          array(
            'backupGlobals' => true,
            'backupStaticAttributes' => false,
            'bootstrap' => '/path/to/bootstrap.php',
            'cacheTokens' => false,
            'colors' => false,
            'stderr' => false,
            'convertErrorsToExceptions' => true,
            'convertNoticesToExceptions' => true,
            'convertWarningsToExceptions' => true,
            'forceCoversAnnotation' => false,
            'mapTestClassNameToCoveredClassName' => false,
            'printerClass' => 'PHPUnit_TextUI_ResultPrinter',
            'stopOnFailure' => false,
            'reportUselessTests' => false,
            'strictCoverage' => false,
            'disallowTestOutput' => false,
            'enforceTimeLimit' => false,
            'testSuiteLoaderClass' => 'PHPUnit_Runner_StandardTestSuiteLoader',
            'verbose' => false,
            'timeoutForSmallTests' => 1,
            'timeoutForMediumTests' => 10,
            'timeoutForLargeTests' => 60
          ),
          $this->configuration->getPHPUnitConfiguration()
        );
    }

    /**
     * @covers PHPUnit_Util_Configuration::getSeleniumBrowserConfiguration
     */
    public function testSeleniumBrowserConfigurationIsReadCorrectly()
    {
        $this->assertEquals(
          array(
            0 =>
            array(
              'name' => 'Firefox on Linux',
              'browser' => '*firefox /usr/lib/firefox/firefox-bin',
              'host' => 'my.linux.box',
              'port' => 4444,
              'timeout' => 30000,
            ),
          ),
          $this->configuration->getSeleniumBrowserConfiguration()
        );
    }

    /**
     * @covers PHPUnit_Util_Configuration::getInstance
     */
    public function testXincludeInConfiguration()
    {
        $configurationWithXinclude = PHPUnit_Util_Configuration::getInstance(
          dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'configuration_xinclude.xml'
        );

        $this->assertConfigurationEquals(
            $this->configuration,
            $configurationWithXinclude
        );
    }

    /**
     * Asserts that the values in $actualConfiguration equal $expectedConfiguration.
     *
     * @param PHPUnit_Util_Configuration $expectedConfiguration
     * @param PHPUnit_Util_Configuration $actualConfiguration
     * @return void
     */
    protected function assertConfigurationEquals( PHPUnit_Util_Configuration $expectedConfiguration, PHPUnit_Util_Configuration $actualConfiguration )
    {
        $this->assertEquals(
            $expectedConfiguration->getFilterConfiguration(),
            $actualConfiguration->getFilterConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getGroupConfiguration(),
            $actualConfiguration->getGroupConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getListenerConfiguration(),
            $actualConfiguration->getListenerConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getLoggingConfiguration(),
            $actualConfiguration->getLoggingConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getPHPConfiguration(),
            $actualConfiguration->getPHPConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getPHPUnitConfiguration(),
            $actualConfiguration->getPHPUnitConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getSeleniumBrowserConfiguration(),
            $actualConfiguration->getSeleniumBrowserConfiguration()
        );

        $this->assertEquals(
            $expectedConfiguration->getTestSuiteConfiguration(),
            $actualConfiguration->getTestSuiteConfiguration()
        );
    }
}
