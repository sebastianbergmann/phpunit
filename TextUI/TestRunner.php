<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: TestRunner.php 539 2006-02-13 16:08:42Z sb $
//

if (!defined('PHPUnit2_MAIN_METHOD')) {
    define('PHPUnit2_MAIN_METHOD', 'PHPUnit2_TextUI_TestRunner::main');
}

require_once 'Console/Getopt.php';
require_once 'Benchmark/Timer.php';

require_once 'PHPUnit2/Extensions/CodeCoverage/Renderer.php';
require_once 'PHPUnit2/Extensions/Log/XML.php';
require_once 'PHPUnit2/Extensions/TestDox/ResultPrinter.php';
require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Runner/BaseTestRunner.php';
require_once 'PHPUnit2/Runner/StandardTestSuiteLoader.php';
require_once 'PHPUnit2/Runner/TestSuiteLoader.php';
require_once 'PHPUnit2/Runner/Version.php';
require_once 'PHPUnit2/TextUI/ResultPrinter.php';
require_once 'PHPUnit2/Util/Skeleton.php';

/**
 * A TestRunner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  TextUI
 */
class PHPUnit2_TextUI_TestRunner extends PHPUnit2_Runner_BaseTestRunner {
    // {{{ Constants

    const SUCCESS_EXIT   = 0;
    const FAILURE_EXIT   = 1;
    const EXCEPTION_EXIT = 2;

    // }}}
    // {{{ Instance Variables

    /**
    * @var    PHPUnit2_Runner_TestSuiteLoader
    * @access private
    */
    private $loader = NULL;

    /**
    * @var    PHPUnit2_TextUI_ResultPrinter
    * @access private
    */
    private $printer = NULL;

    /**
    * @var    boolean
    * @access private
    * @static
    */
    private static $versionStringPrinted = FALSE;

    // }}}
    // {{{ public static function main()

    /**
    * @access public
    * @static
    */
    public static function main() {
        $aTestRunner = new PHPUnit2_TextUI_TestRunner;

        try {
            $result = $aTestRunner->start($_SERVER['argv']);

            if (!$result->wasSuccessful()) {
                exit(self::FAILURE_EXIT);
            }

            exit(self::SUCCESS_EXIT);
        }

        catch (Exception $e) {
            self::printVersionString();
            print $e->getMessage();
            exit(self::EXCEPTION_EXIT);
        }
    }

    // }}}
    // {{{ protected function start($arguments)

    /**
    * @param  array $arguments
    * @access protected
    */
    protected function start($arguments) {
        $coverageDataFile = FALSE;
        $coverageHTMLFile = FALSE;
        $coverageTextFile = FALSE;
        $testdoxHTMLFile  = FALSE;
        $testdoxTextFile  = FALSE;
        $xmlLogfile       = FALSE;
        $wait             = FALSE;

        $possibleOptions = array(
          'help',
          'loader=',
          'skeleton',
          'testdox-html=',
          'testdox-text=',
          'version',
          'wait'
        );

        if (extension_loaded('dom')) {
            $possibleOptions[] = 'log-xml=';
        }

        if (extension_loaded('xdebug')) {
            $possibleOptions[] = 'coverage-data=';
            $possibleOptions[] = 'coverage-html=';
            $possibleOptions[] = 'coverage-text=';
        }

        $options = Console_Getopt::getopt(
          $arguments,
          '',
          $possibleOptions
        );

        if (PEAR::isError($options)) {
            $this->showError($options->getMessage());
        }

        $test     = isset($options[1][0]) ? $options[1][0] : FALSE;
        $testFile = isset($options[1][1]) ? $options[1][1] : $test . '.php';

        foreach ($options[0] as $option) {
            switch ($option[0]) {
                case '--coverage-data': {
                    $coverageDataFile = $option[1];
                }
                break;

                case '--coverage-html': {
                    $coverageHTMLFile = $option[1];
                }
                break;

                case '--coverage-text': {
                    $coverageTextFile = $option[1];
                }
                break;

                case '--help': {
                    $this->showHelp();
                    exit(self::SUCCESS_EXIT);
                }
                break;

                case '--testdox-html': {
                    $testdoxHTMLFile = $option[1];
                }
                break;

                case '--testdox-text': {
                    $testdoxTextFile = $option[1];
                }
                break;

                case '--loader': {
                    if (!class_exists($option[1])) {
                        @include_once(str_replace('_', '/', $option[1]) . '.php');
                    }

                    if (class_exists($option[1])) {
                        $class = new ReflectionClass($option[1]);

                        if ($class->implementsInterface('PHPUnit2_Runner_TestSuiteLoader') &&
                            $class->isInstantiable()) {
                            $this->loader = $class->newInstance();
                        }
                    }

                    if ($this->loader === NULL) {
                        $this->showError(
                          sprintf(
                            'Could not use "%s" as loader.',

                            $option[1]
                          )
                        );
                    }
                }
                break;

                case '--log-xml': {
                    $xmlLogfile = $option[1];
                }
                break;

                case '--skeleton': {
                    if ($test !== FALSE) {
                        self::printVersionString();

                        try {
                            $skeleton = new PHPUnit2_Util_Skeleton($test, $testFile);
                            $skeleton->write();

                            printf(
                              "Wrote test class skeleton for %s to %s.\n",
                              $test,
                              $test . 'Test.php'
                            );

                            exit(self::SUCCESS_EXIT);
                        }

                        catch (Exception $e) {
                            printf(
                              "Could not write test class skeleton for %s to %s.\n",
                              $test,
                              $test . 'Test.php'
                            );

                            exit(self::FAILURE_EXIT);
                        }
                    }
                }
                break;

                case '--version': {
                    self::printVersionString();
                    exit(self::SUCCESS_EXIT);
                }
                break;

                case '--wait': {
                    $wait = TRUE;
                }
                break;
            }
        }

        if ($test === FALSE) {
            $this->showHelp();

            exit(self::SUCCESS_EXIT);
        }

        try {
            return $this->doRun(
              $this->getTest($test, $testFile),
              $coverageDataFile,
              $coverageHTMLFile,
              $coverageTextFile,
              $testdoxHTMLFile,
              $testdoxTextFile,
              $xmlLogfile,
              $wait
            );
        }

        catch (Exception $e) {
            throw new Exception(
              'Could not create and run test suite: ' . $e->getMessage()
            );
        }
    }

    // }}}
    // {{{ public static function run($test, $coverageDataFile = FALSE, $coverageHTMLFile = FALSE, $coverageTextFile = FALSE, $testdoxHTMLFile = FALSE, $testdoxTextFile = FALSE, $xmlLogfile = FALSE, $wait = FALSE)

    /**
    * @param  mixed   $test
    * @param  mixed   $coverageDataFile
    * @param  mixed   $testdoxHTMLFile
    * @param  mixed   $testdoxTextFile
    * @param  mixed   $xmlLogfile
    * @param  boolean $wait
    * @access public
    * @static
    */
    public static function run($test, $coverageDataFile = FALSE, $coverageHTMLFile = FALSE, $coverageTextFile = FALSE, $testdoxHTMLFile = FALSE, $testdoxTextFile = FALSE, $xmlLogfile = FALSE, $wait = FALSE) {
        if ($test instanceof ReflectionClass) {
            $test = new PHPUnit2_Framework_TestSuite($test);
        }

        if ($test instanceof PHPUnit2_Framework_Test) {
            $aTestRunner = new PHPUnit2_TextUI_TestRunner;

            return $aTestRunner->doRun(
              $test,
              $coverageDataFile,
              $coverageHTMLFile,
              $coverageTextFile,
              $testdoxHTMLFile,
              $testdoxTextFile,
              $xmlLogfile,
              $wait
            );
        }
    }

    // }}}
    // {{{ public static function runAndWait(PHPUnit2_Framework_Test $suite)

    /**
    * Runs a single test and waits until the user types RETURN.
    *
    * @param  PHPUnit2_Framework_Test $suite
    * @access public
    * @static
    */
    public static function runAndWait(PHPUnit2_Framework_Test $suite) {
        $aTestRunner = new PHPUnit2_TextUI_TestRunner;

        $aTestRunner->doRun(
          $suite,
          FALSE,
          FALSE,
          FALSE,
          FALSE,
          FALSE,
          FALSE,
          TRUE
        );
    }

    // }}}
    // {{{ protected TestResult createTestResult()

    /**
    * @return PHPUnit2_Framework_TestResult
    * @access protected
    */
    protected function createTestResult() {
        return new PHPUnit2_Framework_TestResult;
    }

    // }}}
    // {{{ public function doRun(PHPUnit2_Framework_Test $suite, $coverageDataFile = FALSE, $coverageHTMLFile = FALSE, $coverageTextFile = FALSE, $testdoxHTMLFile = FALSE, $testdoxTextFile = FALSE, $xmlLogfile = FALSE, $wait = FALSE)

    /**
    * @param  PHPUnit2_Framework_Test $suite
    * @param  mixed                   $coverageDataFile
    * @param  mixed                   $coverageHTMLFile
    * @param  mixed                   $coverageTextFile
    * @param  mixed                   $testdoxHTMLFile
    * @param  mixed                   $testdoxTextFile
    * @param  mixed                   $xmlLogfile
    * @param  boolean                 $wait
    * @return PHPUnit2_Framework_TestResult
    * @access public
    */
    public function doRun(PHPUnit2_Framework_Test $suite, $coverageDataFile = FALSE, $coverageHTMLFile = FALSE, $coverageTextFile = FALSE, $testdoxHTMLFile = FALSE, $testdoxTextFile = FALSE, $xmlLogfile = FALSE, $wait = FALSE) {
        $result = $this->createTestResult();
        $timer  = new Benchmark_Timer;

        if ($this->printer === NULL) {
            $this->printer = new PHPUnit2_TextUI_ResultPrinter;
        }

        $this->printer->write(
          PHPUnit2_Runner_Version::getVersionString() . "\n\n"
        );

        $result->addListener($this->printer);

        if ($testdoxHTMLFile !== FALSE) {
            $result->addListener(
              PHPUnit2_Extensions_TestDox_ResultPrinter::factory(
                'HTML',
                $testdoxHTMLFile
              )
            );
        }

        if ($testdoxTextFile !== FALSE) {
            $result->addListener(
              PHPUnit2_Extensions_TestDox_ResultPrinter::factory(
                'Text',
                $testdoxTextFile
              )
            );
        }

        if ($xmlLogfile !== FALSE) {
            $result->addListener(
              new PHPUnit2_Extensions_Log_XML($xmlLogfile)
            );
        }

        $timer->start();
        $suite->run($result);
        $timer->stop();
        $timeElapsed = $timer->timeElapsed();

        $this->pause($wait);

        $this->printer->printResult($result, $timeElapsed);

        $this->handleCodeCoverageInformation(
          $result,
          $coverageDataFile,
          $coverageHTMLFile,
          $coverageTextFile
        );

        return $result;
    }

    // }}}
    // {{{ public function getLoader()

    /**
    * Returns the loader to be used.
    *
    * @return PHPUnit2_Runner_TestSuiteLoader
    * @access protected
    * @since  2.2.0
    */
    public function getLoader() {
        if ($this->loader === NULL) {
            $this->loader = new PHPUnit2_Runner_StandardTestSuiteLoader;
        }

        return $this->loader;
    }

    // }}}
    // {{{ protected function handleCodeCoverageInformation(PHPUnit2_Framework_TestResult $result, $coverageDataFile, $coverageHTMLFile, $coverageTextFile)

    /**
    * @param  PHPUnit2_Framework_TestResult $result
    * @param  mixed                         $coverageDataFile
    * @param  mixed                         $coverageHTMLFile
    * @param  mixed                         $coverageTextFile
    * @access protected
    * @since  2.1.0
    */
    protected function handleCodeCoverageInformation(PHPUnit2_Framework_TestResult $result, $coverageDataFile, $coverageHTMLFile, $coverageTextFile) {
        if ($coverageDataFile !== FALSE &&
            $fp = fopen($coverageDataFile, 'w')) {
            fputs($fp, serialize($result->getCodeCoverageInformation()));
            fclose($fp);
        }

        if ($coverageHTMLFile !== FALSE) {
            $renderer = PHPUnit2_Extensions_CodeCoverage_Renderer::factory(
              'HTML',
              $result->getCodeCoverageInformation()
            );

            $renderer->renderToFile($coverageHTMLFile);
        }

        if ($coverageTextFile !== FALSE) {
            $renderer = PHPUnit2_Extensions_CodeCoverage_Renderer::factory(
              'Text',
              $result->getCodeCoverageInformation()
            );

            $renderer->renderToFile($coverageTextFile);
        }
    }

    // }}}
    // {{{ public function showError($message)

    /**
    * @access public
    */
    public function showError($message) {
        self::printVersionString();
        print $message . "\n";

        exit(self::FAILURE_EXIT);
    }

    // }}}
    // {{{ public function showHelp()

    /**
    * @access public
    */
    public function showHelp() {
        self::printVersionString();
        print "Usage: phpunit [switches] UnitTest [UnitTest.php]\n";

        if (extension_loaded('xdebug')) {
            print "  --coverage-data <file> Write Code Coverage data in raw format to file.\n" .
                  "  --coverage-html <file> Write Code Coverage data in HTML format to file.\n" .
                  "  --coverage-text <file> Write Code Coverage data in text format to file.\n\n";
        }

        print "  --testdox-html <file>  Log test progress in TestDox/HTML format to file.\n" .
              "  --testdox-text <file>  Log test progress in TestDox/Text format to file.\n";

        if (extension_loaded('dom')) {
            print "  --log-xml <file>       Log test progress in XML format to file.\n\n";
        }

        print "  --loader <loader>      TestSuiteLoader implementation to use.\n\n" .
              "  --skeleton             Generate skeleton UnitTest class for Unit in Unit.php.\n\n" .
              "  --wait                 Waits for a keystroke after each test.\n\n" .
              "  --help                 Prints this usage information.\n" .
              "  --version              Prints the version and exits.\n";
    }

    // }}}
    // {{{ protected function pause($wait)

    /**
    * @param  boolean $wait
    * @access protected
    */
    protected function pause($wait) {
        if (!$wait) {
            return;
        }

        $this->printer->printWaitPrompt();

        fgets(STDIN);
    }

    // }}}
    // {{{ public function setPrinter(PHPUnit2_TextUI_ResultPrinter $resultPrinter)

    /**
    * @param  PHPUnit2_TextUI_ResultPrinter $resultPrinter
    * @access public
    */
    public function setPrinter(PHPUnit2_TextUI_ResultPrinter $resultPrinter) {
        $this->printer = $resultPrinter;
    }

    // }}}
    // {{{ public function testStarted($testName)

    /**
    * A test started.
    *
    * @param  string  $testName
    * @access public
    */
    public function testStarted($testName) {
    }

    // }}}
    // {{{ public function testEnded($testName)

    /**
    * A test ended.
    *
    * @param  string  $testName
    * @access public
    */
    public function testEnded($testName) {
    }

    // }}}
    // {{{ public function testFailed($status, PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)

    /**
    * A test failed.
    *
    * @param  integer                                 $status
    * @param  PHPUnit2_Framework_Test                 $test
    * @param  PHPUnit2_Framework_AssertionFailedError $e
    * @access public
    */
    public function testFailed($status, PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
    }

    // }}}
    // {{{ protected function runFailed($message)

    /**
    * Override to define how to handle a failed loading of
    * a test suite.
    *
    * @param  string  $message
    * @access protected
    */
    protected function runFailed($message) {
        self::printVersionString();
        print $message;
        exit(self::FAILURE_EXIT);
    }

    // }}}
    // {{{ private static function printVersionString()

    /**
    * @access private
    * @since  2.2.0
    */
    private static function printVersionString() {
        if (!self::$versionStringPrinted) {
            print PHPUnit2_Runner_Version::getVersionString() . "\n\n";
            self::$versionStringPrinted = TRUE;
        }
    }

    // }}}
}

if (PHPUnit2_MAIN_METHOD == 'PHPUnit2_TextUI_TestRunner::main') {
    PHPUnit2_TextUI_TestRunner::main();
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
