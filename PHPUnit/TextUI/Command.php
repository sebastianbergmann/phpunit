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
     */
    public static function main($exit = TRUE)
    {
        $arguments = self::handleArguments();
        $runner    = new PHPUnit_TextUI_TestRunner;

        if (is_object($arguments['test']) && $arguments['test'] instanceof PHPUnit_Framework_Test) {
            $suite = $arguments['test'];
        } else {
            $suite = $runner->getTest(
              $arguments['test'],
              $arguments['testFile'],
              $arguments['syntaxCheck']
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
                $arguments['testFile']
            );

            $result = $skeleton->generate(TRUE);

            if (!$result['incomplete']) {
                eval(str_replace(array('<?php', '?>'), '', $result['code']));
                $suite = new PHPUnit_Framework_TestSuite($arguments['test'] . 'Test');
            }
        }

        if ($arguments['listGroups']) {
            PHPUnit_TextUI_TestRunner::printVersionString();

            print "Available test group(s):\n";

            $groups = $suite->getGroups();
            sort($groups);

            foreach ($groups as $group) {
                print " - $group\n";
            }

            exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
        }

        try {
            $result = $runner->doRun(
              $suite,
              $arguments
            );
        }

        catch (Exception $e) {
            throw new RuntimeException(
              'Could not create and run test suite: ' . $e->getMessage()
            );
        }

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
     */
    protected static function handleArguments()
    {
        $arguments = array(
          'listGroups'  => FALSE,
          'syntaxCheck' => TRUE
        );

        $longOptions = array(
          'ansi',
          'colors',
          'bootstrap=',
          'configuration=',
          'coverage-html=',
          'coverage-clover=',
          'coverage-source=',
          'coverage-xml=',
          'debug',
          'exclude-group=',
          'filter=',
          'group=',
          'help',
          'list-groups',
          'loader=',
          'log-graphviz=',
          'log-json=',
          'log-metrics=',
          'log-pmd=',
          'log-tap=',
          'log-xml=',
          'repeat=',
          'report=',
          'skeleton',
          'skeleton-class',
          'skeleton-test',
          'stop-on-failure',
          'story',
          'story-html=',
          'story-text=',
          'tap',
          'test-db-dsn=',
          'test-db-log-rev=',
          'test-db-log-prefix=',
          'test-db-log-info=',
          'testdox',
          'testdox-html=',
          'testdox-text=',
          'no-syntax-check',
          'verbose',
          'version',
          'wait'
        );

        try {
            $options = PHPUnit_Util_Getopt::getopt(
              $_SERVER['argv'],
              'd:',
              $longOptions
            );
        }

        catch (RuntimeException $e) {
            PHPUnit_TextUI_TestRunner::showError($e->getMessage());
        }

        if (isset($options[1][0])) {
            $arguments['test'] = $options[1][0];
        }

        if (isset($options[1][1])) {
            $arguments['testFile'] = $options[1][1];
        } else {
            $arguments['testFile'] = '';
        }

        if (isset($arguments['test']) && is_file($arguments['test'])) {
            $arguments['testFile'] = realpath($arguments['test']);
            $arguments['test']     = substr($arguments['test'], 0, strrpos($arguments['test'], '.'));
        }

        $skeletonClass = FALSE;
        $skeletonTest  = FALSE;

        foreach ($options[0] as $option) {
            switch ($option[0]) {
                case '--ansi':
                case '--colors': {
                    $arguments['colors'] = TRUE;
                }
                break;

                case '--bootstrap': {
                    $arguments['bootstrap'] = $option[1];
                }
                break;

                case '--configuration': {
                    $arguments['configuration'] = $option[1];
                }
                break;

                case '--coverage-clover':
                case '--coverage-xml': {
                    if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
                        $arguments['coverageClover'] = $option[1];
                    } else {
                        if (!extension_loaded('tokenizer')) {
                            self::showMissingDependency('The tokenizer extension is not loaded.');
                        } else {
                            self::showMissingDependency('The Xdebug extension is not loaded.');
                        }
                    }
                }
                break;

                case '--coverage-source': {
                    if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
                        $arguments['coverageSource'] = $option[1];
                    } else {
                        if (!extension_loaded('tokenizer')) {
                            self::showMissingDependency('The tokenizer extension is not loaded.');
                        } else {
                            self::showMissingDependency('The Xdebug extension is not loaded.');
                        }
                    }
                }
                break;

                case '--coverage-html':
                case '--report': {
                    if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
                        $arguments['reportDirectory'] = $option[1];
                    } else {
                        if (!extension_loaded('tokenizer')) {
                            self::showMissingDependency('The tokenizer extension is not loaded.');
                        } else {
                            self::showMissingDependency('The Xdebug extension is not loaded.');
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
                    $arguments['debug'] = TRUE;
                }
                break;

                case '--help': {
                    self::showHelp();
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                }
                break;

                case '--filter': {
                    $arguments['filter'] = $option[1];
                }
                break;

                case '--group': {
                    $arguments['groups'] = explode(',', $option[1]);
                }
                break;

                case '--exclude-group': {
                    $arguments['excludeGroups'] = explode(',', $option[1]);
                }
                break;

                case '--list-groups': {
                    $arguments['listGroups'] = TRUE;
                }
                break;

                case '--loader': {
                    self::handleLoader($option[1]);
                }
                break;

                case '--log-json': {
                    $arguments['jsonLogfile'] = $option[1];
                }
                break;

                case '--log-graphviz': {
                    if (PHPUnit_Util_Filesystem::fileExistsInIncludePath('Image/GraphViz.php')) {
                        $arguments['graphvizLogfile'] = $option[1];
                    } else {
                        self::showMissingDependency('The Image_GraphViz package is not installed.');
                    }
                }
                break;

                case '--log-tap': {
                    $arguments['tapLogfile'] = $option[1];
                }
                break;

                case '--log-xml': {
                    $arguments['xmlLogfile'] = $option[1];
                }
                break;

                case '--log-pmd': {
                    if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
                        $arguments['pmdXML'] = $option[1];
                    } else {
                        if (!extension_loaded('tokenizer')) {
                            self::showMissingDependency('The tokenizer extension is not loaded.');
                        } else {
                            self::showMissingDependency('The Xdebug extension is not loaded.');
                        }
                    }
                }
                break;

                case '--log-metrics': {
                    if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
                        $arguments['metricsXML'] = $option[1];
                    } else {
                        if (!extension_loaded('tokenizer')) {
                            self::showMissingDependency('The tokenizer extension is not loaded.');
                        } else {
                            self::showMissingDependency('The Xdebug extension is not loaded.');
                        }
                    }
                }
                break;

                case '--repeat': {
                    $arguments['repeat'] = (int)$option[1];
                }
                break;

                case '--stop-on-failure': {
                    $arguments['stopOnFailure'] = TRUE;
                }
                break;

                case '--test-db-dsn': {
                    if (extension_loaded('pdo')) {
                        $arguments['testDatabaseDSN'] = $option[1];
                    } else {
                        self::showMissingDependency('The PDO extension is not loaded.');
                    }
                }
                break;

                case '--test-db-log-rev': {
                    if (extension_loaded('pdo')) {
                        $arguments['testDatabaseLogRevision'] = $option[1];
                    } else {
                        self::showMissingDependency('The PDO extension is not loaded.');
                    }
                }
                break;

                case '--test-db-prefix': {
                    if (extension_loaded('pdo')) {
                        $arguments['testDatabasePrefix'] = $option[1];
                    } else {
                        self::showMissingDependency('The PDO extension is not loaded.');
                    }
                }
                break;

                case '--test-db-log-info': {
                    if (extension_loaded('pdo')) {
                        $arguments['testDatabaseLogInfo'] = $option[1];
                    } else {
                        self::showMissingDependency('The PDO extension is not loaded.');
                    }
                }
                break;

                case '--skeleton':
                case '--skeleton-test': {
                    $skeletonTest  = TRUE;
                    $skeletonClass = FALSE;
                }
                break;

                case '--skeleton-class': {
                    $skeletonClass = TRUE;
                    $skeletonTest  = FALSE;
                }
                break;

                case '--tap': {
                    require_once 'PHPUnit/Util/Log/TAP.php';

                    $arguments['printer'] = new PHPUnit_Util_Log_TAP;
                }
                break;

                case '--story': {
                    require_once 'PHPUnit/Extensions/Story/ResultPrinter/Text.php';

                    $arguments['printer'] = new PHPUnit_Extensions_Story_ResultPrinter_Text;
                }
                break;

                case '--story-html': {
                    $arguments['storyHTMLFile'] = $option[1];
                }
                break;

                case '--story-text': {
                    $arguments['storyTextFile'] = $option[1];
                }
                break;

                case '--testdox': {
                    require_once 'PHPUnit/Util/TestDox/ResultPrinter/Text.php';

                    $arguments['printer'] = new PHPUnit_Util_TestDox_ResultPrinter_Text;
                }
                break;

                case '--testdox-html': {
                    $arguments['testdoxHTMLFile'] = $option[1];
                }
                break;

                case '--testdox-text': {
                    $arguments['testdoxTextFile'] = $option[1];
                }
                break;

                case '--no-syntax-check': {
                    $arguments['syntaxCheck'] = FALSE;
                }
                break;

                case '--verbose': {
                    $arguments['verbose'] = TRUE;
                }
                break;

                case '--version': {
                    PHPUnit_TextUI_TestRunner::printVersionString();
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                }
                break;

                case '--wait': {
                    $arguments['wait'] = TRUE;
                }
                break;
            }
        }

        if (isset($arguments['bootstrap'])) {
            PHPUnit_Util_Fileloader::load($arguments['bootstrap']);
        }

        if (!isset($arguments['configuration']) && file_exists('phpunit.xml')) {
            $arguments['configuration'] = realpath('phpunit.xml');
        }

        if (isset($arguments['configuration'])) {
            $configuration = new PHPUnit_Util_Configuration(
              $arguments['configuration']
            );

            $configuration->handlePHPConfiguration();

            if (!isset($arguments['bootstrap'])) {
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

            if (!isset($arguments['test'])) {
                $testSuite = $configuration->getTestSuiteConfiguration(
                  $arguments['syntaxCheck']
                );

                if ($testSuite !== NULL) {
                    $arguments['test'] = $testSuite;
                }
            }
        }

        if (isset($arguments['test']) && is_string($arguments['test']) && substr($arguments['test'], -5, 5) == '.phpt') {
            require_once 'PHPUnit/Extensions/PhptTestCase.php';

            $test = new PHPUnit_Extensions_PhptTestCase($arguments['test']);

            $arguments['test'] = new PHPUnit_Framework_TestSuite;
            $arguments['test']->addTest($test);
        }

        if (!isset($arguments['test']) ||
            (isset($arguments['testDatabaseLogRevision']) && !isset($arguments['testDatabaseDSN']))) {
            self::showHelp();
            exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
        }

        if ($skeletonClass || $skeletonTest) {
            if (isset($arguments['test']) && $arguments['test'] !== FALSE) {
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
                        if (isset($options[1][$i])) {
                            $args[] = $options[1][$i];
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
                self::showHelp();
                exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
            }
        }

        return $arguments;
    }

    /**
     * @param  string  $loaderName
     */
    protected static function handleLoader($loaderName)
    {
        if (!class_exists($loaderName, FALSE)) {
            PHPUnit_Util_Fileloader::checkAndLoad(
              str_replace('_', '/', $loaderName) . '.php'
            );
        }

        if (class_exists($loaderName, FALSE)) {
            $class = new ReflectionClass($loaderName);

            if ($class->implementsInterface('PHPUnit_Runner_TestSuiteLoader') &&
                $class->isInstantiable()) {
                $loader = $class->newInstance();
            }
        }

        if (!isset($loader)) {
            PHPUnit_TextUI_TestRunner::showError(
              sprintf(
                'Could not use "%s" as loader.',

                $loaderName
              )
            );
        }

        PHPUnit_TextUI_TestRunner::setLoader($loader);
    }

    /**
     * @param string $message
     */
    public static function showMissingDependency($message)
    {
        PHPUnit_TextUI_TestRunner::printVersionString();
        print $message . "\n";
        exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
    }

    /**
     */
    public static function showHelp()
    {
        PHPUnit_TextUI_TestRunner::printVersionString();

        print <<<EOT
Usage: phpunit [switches] UnitTest [UnitTest.php]
       phpunit [switches] <directory>

  --log-graphviz <file>    Log test execution in GraphViz markup.
  --log-json <file>        Log test execution in JSON format.
  --log-tap <file>         Log test execution in TAP format to file.
  --log-xml <file>         Log test execution in XML format to file.
  --log-metrics <file>     Write metrics report in XML format.
  --log-pmd <file>         Write violations report in PMD XML format.

  --coverage-html <dir>    Generate code coverage report in HTML format.
  --coverage-clover <file> Write code coverage data in Clover XML format.
  --coverage-source <dir>  Write code coverage / source data in XML format.

  --test-db-dsn <dsn>      DSN for the test database.
  --test-db-log-rev <rev>  Revision information for database logging.
  --test-db-prefix ...     Prefix that should be stripped from filenames.
  --test-db-log-info ...   Additional information for database logging.

  --story-html <file>      Write Story/BDD results in HTML format to file.
  --story-text <file>      Write Story/BDD results in Text format to file.

  --testdox-html <file>    Write agile documentation in HTML format to file.
  --testdox-text <file>    Write agile documentation in Text format to file.

  --filter <pattern>       Filter which tests to run.
  --group ...              Only runs tests from the specified group(s).
  --exclude-group ...      Exclude tests from the specified group(s).
  --list-groups            List available test groups.

  --loader <loader>        TestSuiteLoader implementation to use.
  --repeat <times>         Runs the test(s) repeatedly.

  --story                  Report test execution progress in Story/BDD format.
  --tap                    Report test execution progress in TAP format.
  --testdox                Report test execution progress in TestDox format.

  --no-syntax-check        Disable syntax check of test source files.
  --stop-on-failure        Stop execution upon first error or failure.
  --colors                 Use colors in output.
  --verbose                Output more verbose information.
  --wait                   Waits for a keystroke after each test.

  --skeleton-class         Generate Unit class for UnitTest in UnitTest.php.
  --skeleton-test          Generate UnitTest class for Unit in Unit.php.

  --help                   Prints this usage information.
  --version                Prints the version and exits.

  --bootstrap <file>       A "bootstrap" PHP file that is run before the tests.
  --configuration <file>   Read configuration from XML file.
  -d key[=value]           Sets a php.ini value.

EOT;
    }
}
?>
