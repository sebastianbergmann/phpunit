<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Configuration.php';
require_once 'PHPUnit/Util/Fileloader.php';
require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Getopt.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestRunner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_TextUI_Command
{
    /**
     * @var array
     */
    protected $arguments = array(
      'listGroups'              => FALSE,
      'loader'                  => NULL,
      'useDefaultConfiguration' => TRUE
    );

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var array
     */
    protected $longOptions = array(
      'bootstrap=' => NULL,
      'colors' => NULL,
      'configuration=' => NULL,
      'coverage-clover=' => NULL,
      'coverage-html=' => NULL,
      'debug' => NULL,
      'exclude-group=' => NULL,
      'filter=' => NULL,
      'group=' => NULL,
      'help' => NULL,
      'include-path=' => NULL,
      'list-groups' => NULL,
      'loader=' => NULL,
      'log-json=' => NULL,
      'log-junit=' => NULL,
      'log-tap=' => NULL,
      'no-configuration' => NULL,
      'no-globals-backup' => NULL,
      'no-static-backup' => NULL,
      'process-isolation' => NULL,
      'skeleton-class' => NULL,
      'skeleton-test' => NULL,
      'stop-on-failure' => NULL,
      'story' => NULL,
      'story-html=' => NULL,
      'story-text=' => NULL,
      'tap' => NULL,
      'testdox' => NULL,
      'testdox-html=' => NULL,
      'testdox-text=' => NULL,
      'verbose' => NULL,
      'version' => NULL,
      'wait' => NULL
    );

    /**
     * @param boolean $exit
     */
    public static function main($exit = TRUE)
    {
        $command = new PHPUnit_TextUI_Command;
        $command->run($_SERVER['argv'], $exit);
    }

    /**
     * @param array   $argv
     * @param boolean $exit
     */
    public function run(array $argv, $exit = TRUE)
    {
        $this->handleArguments($argv);

        $runner = new PHPUnit_TextUI_TestRunner($this->arguments['loader']);

        if (is_object($this->arguments['test']) && $this->arguments['test'] instanceof PHPUnit_Framework_Test) {
            $suite = $this->arguments['test'];
        } else {
            $suite = $runner->getTest(
              $this->arguments['test'],
              $this->arguments['testFile']
            );
        }

        if ($suite->testAt(0) instanceof PHPUnit_Framework_Warning &&
            strpos($suite->testAt(0)->getMessage(), 'No tests found in class') !== FALSE) {
            $message   = $suite->testAt(0)->getMessage();
            $start     = strpos($message, '"') + 1;
            $end       = strpos($message, '"', $start);
            $className = substr($message, $start, $end - $start);

            require_once 'PHPUnit/Util/Skeleton/Test.php';

            $skeleton = new PHPUnit_Util_Skeleton_Test(
              $className,
              $this->arguments['testFile']
            );

            $result = $skeleton->generate(TRUE);

            if (!$result['incomplete']) {
                eval(str_replace(array('<?php', '?>'), '', $result['code']));
                $suite = new PHPUnit_Framework_TestSuite($this->arguments['test'] . 'Test');
            }
        }

        if ($this->arguments['listGroups']) {
            PHPUnit_TextUI_TestRunner::printVersionString();

            print "Available test group(s):\n";

            $groups = $suite->getGroups();
            sort($groups);

            foreach ($groups as $group) {
                print " - $group\n";
            }

            exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
        }

        unset($this->arguments['test']);
        unset($this->arguments['testFile']);

        $result = $runner->doRun($suite, $this->arguments);

        if ($exit) {
            if ($result->wasSuccessful()) {
                exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
            }

            else if ($result->errorCount() > 0) {
                exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
            }

            else {
                exit(PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
            }
        }
    }

    /**
     * Handles the command-line arguments.
     *
     * A child class of PHPUnit_TextUI_Command can hook into the argument
     * parsing by adding the switch(es) to the $longOptions array and point to a
     * callback method that handles the switch(es) in the child class like this
     *
     * <code>
     * <?php
     * class MyCommand extends PHPUnit_TextUI_Command
     * {
     *     public function __construct()
     *     {
     *         $this->longOptions['--my-switch'] = 'myHandler';
     *     }
     *
     *     // --my-switch foo -> myHandler('foo')
     *     protected function myHandler($value)
     *     {
     *     }
     * }
     * </code>
     *
     * @param  array $argv
     * @return array
     */
    protected function handleArguments(array $argv)
    {
        try {
            $this->options = PHPUnit_Util_Getopt::getopt(
              $argv,
              'd:',
              array_keys($this->longOptions)
            );
        }

        catch (RuntimeException $e) {
            PHPUnit_TextUI_TestRunner::showError($e->getMessage());
        }

        $skeletonClass = FALSE;
        $skeletonTest  = FALSE;

        foreach ($this->options[0] as $option) {
            switch ($option[0]) {
                case '--bootstrap': {
                    $this->arguments['bootstrap'] = $option[1];
                }
                break;

                case '--colors': {
                    $this->arguments['colors'] = TRUE;
                }
                break;

                case '--configuration': {
                    $this->arguments['configuration'] = $option[1];
                }
                break;

                case '--coverage-html': {
                    if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
                        $this->arguments['reportDirectory'] = $option[1];
                    } else {
                        if (!extension_loaded('tokenizer')) {
                            $this->showMessage('The tokenizer extension is not loaded.');
                        } else {
                            $this->showMessage('The Xdebug extension is not loaded.');
                        }
                    }
                }
                break;

                case '--coverage-clover': {
                    if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
                        $this->arguments['coverageClover'] = $option[1];
                    } else {
                        if (!extension_loaded('tokenizer')) {
                            $this->showMessage('The tokenizer extension is not loaded.');
                        } else {
                            $this->showMessage('The Xdebug extension is not loaded.');
                        }
                    }
                }
                break;

                case 'd': {
                    $ini = explode('=', $option[1]);

                    if (isset($ini[0])) {
                        if (isset($ini[1])) {
                            ini_set($ini[0], $ini[1]);
                        } else {
                            ini_set($ini[0], TRUE);
                        }
                    }
                }
                break;

                case '--debug': {
                    $this->arguments['debug'] = TRUE;
                }
                break;

                case '--exclude-group': {
                    $this->arguments['excludeGroups'] = explode(',', $option[1]);
                }
                break;

                case '--filter': {
                    $this->arguments['filter'] = $option[1];
                }
                break;

                case '--group': {
                    $this->arguments['groups'] = explode(',', $option[1]);
                }
                break;

                case '--help': {
                    $this->showHelp();
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                }
                break;

                case '--include-path': {
                    $includePath = $option[1];
                }
                break;

                case '--list-groups': {
                    $this->arguments['listGroups'] = TRUE;
                }
                break;

                case '--loader': {
                    $this->arguments['loader'] = $option[1];
                }
                break;

                case '--log-json': {
                    $this->arguments['jsonLogfile'] = $option[1];
                }
                break;

                case '--log-junit': {
                    $this->arguments['junitLogfile'] = $option[1];
                }
                break;

                case '--log-tap': {
                    $this->arguments['tapLogfile'] = $option[1];
                }
                break;

                case '--no-configuration': {
                    $this->arguments['useDefaultConfiguration'] = FALSE;
                }
                break;

                case '--no-globals-backup': {
                    $this->arguments['backupGlobals'] = FALSE;
                }
                break;

                case '--no-static-backup': {
                    $this->arguments['backupStaticAttributes'] = FALSE;
                }
                break;

                case '--process-isolation': {
                    $this->arguments['processIsolation'] = TRUE;
                }
                break;

                case '--skeleton-class': {
                    $skeletonClass = TRUE;
                    $skeletonTest  = FALSE;
                }
                break;

                case '--skeleton-test': {
                    $skeletonTest  = TRUE;
                    $skeletonClass = FALSE;
                }
                break;

                case '--stop-on-failure': {
                    $this->arguments['stopOnFailure'] = TRUE;
                }
                break;

                case '--story': {
                    require_once 'PHPUnit/Extensions/Story/ResultPrinter/Text.php';

                    $this->arguments['printer'] = new PHPUnit_Extensions_Story_ResultPrinter_Text;
                }
                break;

                case '--story-html': {
                    $this->arguments['storyHTMLFile'] = $option[1];
                }
                break;

                case '--story-text': {
                    $this->arguments['storyTextFile'] = $option[1];
                }
                break;

                case '--tap': {
                    require_once 'PHPUnit/Util/Log/TAP.php';

                    $this->arguments['printer'] = new PHPUnit_Util_Log_TAP;
                }
                break;

                case '--testdox': {
                    require_once 'PHPUnit/Util/TestDox/ResultPrinter/Text.php';

                    $this->arguments['printer'] = new PHPUnit_Util_TestDox_ResultPrinter_Text;
                }
                break;

                case '--testdox-html': {
                    $this->arguments['testdoxHTMLFile'] = $option[1];
                }
                break;

                case '--testdox-text': {
                    $this->arguments['testdoxTextFile'] = $option[1];
                }
                break;

                case '--verbose': {
                    $this->arguments['verbose'] = TRUE;
                }
                break;

                case '--version': {
                    PHPUnit_TextUI_TestRunner::printVersionString();
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                }
                break;

                case '--wait': {
                    $this->arguments['wait'] = TRUE;
                }
                break;

                default: {
                    $optionName = str_replace('--', '', $option[0]);

                    if (isset($this->longOptions[$optionName])) {
                        $handler = $this->longOptions[$optionName];
                    }

                    else if (isset($this->longOptions[$optionName . '='])) {
                        $handler = $this->longOptions[$optionName . '='];
                    }

                    if (isset($handler) && is_callable(array($this, $handler))) {
                        $this->$handler($option[1]);
                    }
                }
            }
        }

        $this->handleCustomTestSuite();

        if (!isset($this->arguments['test'])) {
            if (isset($this->options[1][0])) {
                $this->arguments['test'] = $this->options[1][0];
            }

            if (isset($this->options[1][1])) {
                $this->arguments['testFile'] = $this->options[1][1];
            } else {
                $this->arguments['testFile'] = '';
            }

            if (isset($this->arguments['test']) && is_file($this->arguments['test'])) {
                $this->arguments['testFile'] = realpath($this->arguments['test']);
                $this->arguments['test']     = substr($this->arguments['test'], 0, strrpos($this->arguments['test'], '.'));
            }
        }

        if (isset($includePath)) {
            ini_set(
              'include_path',
              $includePath . PATH_SEPARATOR . ini_get('include_path')
            );
        }

        if (isset($this->arguments['bootstrap'])) {
            PHPUnit_Util_Fileloader::load($this->arguments['bootstrap']);
        }

        if ($this->arguments['loader'] !== NULL) {
            $this->arguments['loader'] = $this->handleLoader($this->arguments['loader']);
        }

        if (!isset($this->arguments['configuration']) && $this->arguments['useDefaultConfiguration']) {
            if (file_exists('phpunit.xml')) {
                $this->arguments['configuration'] = realpath('phpunit.xml');
            }

            else if (file_exists('phpunit.xml.dist')) {
                $this->arguments['configuration'] = realpath('phpunit.xml.dist');
            }
        }

        if (isset($this->arguments['configuration'])) {
            $configuration = PHPUnit_Util_Configuration::getInstance(
              $this->arguments['configuration']
            );

            $phpunit = $configuration->getPHPUnitConfiguration();

            if (isset($phpunit['testSuiteLoaderClass'])) {
                if (isset($phpunit['testSuiteLoaderFile'])) {
                    $file = $phpunit['testSuiteLoaderFile'];
                } else {
                    $file = '';
                }

                $this->arguments['loader'] = $this->handleLoader(
                  $phpunit['testSuiteLoaderClass'], $file
                );
            }

            $configuration->handlePHPConfiguration();

            if (!isset($this->arguments['bootstrap'])) {
                $phpunitConfiguration = $configuration->getPHPUnitConfiguration();

                if (isset($phpunitConfiguration['bootstrap'])) {
                    PHPUnit_Util_Fileloader::load($phpunitConfiguration['bootstrap']);
                }
            }

            $browsers = $configuration->getSeleniumBrowserConfiguration();

            if (!empty($browsers)) {
                require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
                PHPUnit_Extensions_SeleniumTestCase::$browsers = $browsers;
            }

            if (!isset($this->arguments['test'])) {
                $testSuite = $configuration->getTestSuiteConfiguration();

                if ($testSuite !== NULL) {
                    $this->arguments['test'] = $testSuite;
                }
            }
        }

        if (isset($this->arguments['test']) && is_string($this->arguments['test']) && substr($this->arguments['test'], -5, 5) == '.phpt') {
            require_once 'PHPUnit/Extensions/PhptTestCase.php';

            $test = new PHPUnit_Extensions_PhptTestCase($this->arguments['test']);

            $this->arguments['test'] = new PHPUnit_Framework_TestSuite;
            $this->arguments['test']->addTest($test);
        }

        if (!isset($this->arguments['test'])) {
            $this->showHelp();
            exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
        }

        if ($skeletonClass || $skeletonTest) {
            if (isset($this->arguments['test']) && $this->arguments['test'] !== FALSE) {
                PHPUnit_TextUI_TestRunner::printVersionString();

                if ($skeletonClass) {
                    require_once 'PHPUnit/Util/Skeleton/Class.php';

                    $class = 'PHPUnit_Util_Skeleton_Class';
                } else {
                    require_once 'PHPUnit/Util/Skeleton/Test.php';

                    $class = 'PHPUnit_Util_Skeleton_Test';
                }

                try {
                    $args      = array();
                    $reflector = new ReflectionClass($class);

                    for ($i = 0; $i <= 3; $i++) {
                        if (isset($this->options[1][$i])) {
                            $args[] = $this->options[1][$i];
                        }
                    }

                    $skeleton = $reflector->newInstanceArgs($args);
                    $skeleton->write();
                }

                catch (Exception $e) {
                    print $e->getMessage() . "\n";
                    exit(PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
                }

                printf(
                  'Wrote skeleton for "%s" to "%s".' . "\n",
                  $skeleton->getOutClassName(),
                  $skeleton->getOutSourceFile()
                );

                exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
            } else {
                $this->showHelp();
                exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
            }
        }
    }

    /**
     * Handles the loading of the PHPUnit_Runner_TestSuiteLoader implementation.
     *
     * @param  string  $loaderClass
     * @param  string  $loaderFile
     */
    protected function handleLoader($loaderClass, $loaderFile = '')
    {
        if (!class_exists($loaderClass, FALSE)) {
            if ($loaderFile == '') {
                $loaderFile = PHPUnit_Util_Filesystem::classNameToFilename(
                  $loaderClass
                );
            }

            $loaderFile = PHPUnit_Util_Filesystem::fileExistsInIncludePath(
              $loaderFile
            );

            if ($loaderFile !== FALSE) {
                require $loaderFile;
            }
        }

        if (class_exists($loaderClass, FALSE)) {
            $class = new ReflectionClass($loaderClass);

            if ($class->implementsInterface('PHPUnit_Runner_TestSuiteLoader') &&
                $class->isInstantiable()) {
                $loader = $class->newInstance();
            }
        }

        if (!isset($loader)) {
            PHPUnit_TextUI_TestRunner::showError(
              sprintf(
                'Could not use "%s" as loader.',

                $loaderClass
              )
            );
        }

        return $loader;
    }

    /**
     * Shows a message.
     *
     * @param string  $message
     * @param boolean $exit
     */
    protected function showMessage($message, $exit = TRUE)
    {
        PHPUnit_TextUI_TestRunner::printVersionString();
        print $message . "\n";

        if ($exit) {
            exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
        } else {
            print "\n";
        }
    }

    /**
     * Show the help message.
     */
    protected function showHelp()
    {
        PHPUnit_TextUI_TestRunner::printVersionString();

        print <<<EOT
Usage: phpunit [switches] UnitTest [UnitTest.php]
       phpunit [switches] <directory>

  --log-junit <file>       Log test execution in JUnit XML format to file.
  --log-tap <file>         Log test execution in TAP format to file.
  --log-json <file>        Log test execution in JSON format.

  --coverage-html <dir>    Generate code coverage report in HTML format.
  --coverage-clover <file> Write code coverage data in Clover XML format.

  --story-html <file>      Write Story/BDD results in HTML format to file.
  --story-text <file>      Write Story/BDD results in Text format to file.

  --testdox-html <file>    Write agile documentation in HTML format to file.
  --testdox-text <file>    Write agile documentation in Text format to file.

  --filter <pattern>       Filter which tests to run.
  --group ...              Only runs tests from the specified group(s).
  --exclude-group ...      Exclude tests from the specified group(s).
  --list-groups            List available test groups.

  --loader <loader>        TestSuiteLoader implementation to use.

  --story                  Report test execution progress in Story/BDD format.
  --tap                    Report test execution progress in TAP format.
  --testdox                Report test execution progress in TestDox format.

  --colors                 Use colors in output.
  --stop-on-failure        Stop execution upon first error or failure.
  --verbose                Output more verbose information.
  --wait                   Waits for a keystroke after each test.

  --skeleton-class         Generate Unit class for UnitTest in UnitTest.php.
  --skeleton-test          Generate UnitTest class for Unit in Unit.php.

  --process-isolation      Run each test in a separate PHP process.
  --no-globals-backup      Do not backup and restore \$GLOBALS.
  --no-static-backup       Do not backup and restore static attributes.

  --bootstrap <file>       A "bootstrap" PHP file that is run before the tests.
  --configuration <file>   Read configuration from XML file.
  --no-configuration       Ignore default configuration file (phpunit.xml).
  --include-path <path(s)> Prepend PHP's include_path with given path(s).
  -d key[=value]           Sets a php.ini value.

  --help                   Prints this usage information.
  --version                Prints the version and exits.

EOT;
    }

    /**
     * Custom callback for test suite discovery.
     */
    protected function handleCustomTestSuite()
    {
    }
}
?>
