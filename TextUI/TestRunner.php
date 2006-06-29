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
    // {{{ Members

    /**
    * @var    PHPUnit2_TextUI_ResultPrinter
    * @access private
    */
    private $printer = NULL;

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
          'log-xml=',
          'skeleton',
          'testdox-html=',
          'testdox-text=',
          'version',
          'wait'
        );

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
            print PHPUnit2_Runner_Version::getVersionString() . "\n\n";
            print $options->getMessage() . "\n";

            exit(self::FAILURE_EXIT);
        }

        $test = isset($options[1][0]) ? $options[1][0] : FALSE;

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

                case '--log-xml': {
                    $xmlLogfile = $option[1];
                }
                break;

                case '--skeleton': {
                    if ($test !== FALSE) {
                        print PHPUnit2_Runner_Version::getVersionString() . "\n\n";

                        try {
                            $skeleton = new PHPUnit2_Util_Skeleton($test);
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
                    print PHPUnit2_Runner_Version::getVersionString() . "\n";
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
              $this->getTest($test),
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

            if ($fp = fopen($coverageHTMLFile, 'w')) {
                fputs(
                  $fp,
                  $renderer->render()
                );

                fclose($fp);
            }
        }

        if ($coverageTextFile !== FALSE) {
            $renderer = PHPUnit2_Extensions_CodeCoverage_Renderer::factory(
              'Text',
              $result->getCodeCoverageInformation()
            );

            if ($fp = fopen($coverageTextFile, 'w')) {
                fputs(
                  $fp,
                  $renderer->render()
                );

                fclose($fp);
            }
        }
    }

    // }}}
    // {{{ public function showHelp()

    /**
    * @access public
    */
    public function showHelp() {
        print PHPUnit2_Runner_Version::getVersionString() . "\n\n" .
              "Usage: phpunit [switches] UnitTest\n";

        if (extension_loaded('xdebug')) {
            print "  --coverage-data <file> Write raw code coverage data to file.\n";
            print "  --coverage-html <file> Write code coverage data in HTML format to file.\n";
            print "  --coverage-text <file> Write code coverage data in text formar to file.\n\n";
        }

        print "  --testdox-html <file>  Log test progress in TestDox/HTML format to file.\n" .
              "  --testdox-text <file>  Log test progress in TestDox/Text format to file.\n" .
              "  --log-xml <file>       Log test progress in XML format to file.\n\n" .
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
    // {{{ protected function runFailed($message)

    /**
    * Override to define how to handle a failed loading of
    * a test suite.
    *
    * @param  string  $message
    * @access protected
    */
    protected function runFailed($message) {
        print $message;
        exit(self::FAILURE_EXIT);
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
}

if (PHPUnit2_MAIN_METHOD == 'PHPUnit2_TextUI_TestRunner::main') {
    PHPUnit2_TextUI_TestRunner::main();
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
