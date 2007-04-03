<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

if (!defined('PHPUnit2_MAIN_METHOD')) {
    define('PHPUnit2_MAIN_METHOD', 'PHPUnit2_TextUI_TestRunner::main');
}

require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/Runner/Version.php';
require_once 'PHPUnit2/Runner/BaseTestRunner.php';
require_once 'PHPUnit2/TextUI/ResultPrinter.php';
require_once 'PHPUnit2/Util/Fileloader.php';

require_once 'Console/Getopt.php';
require_once 'Benchmark/Timer.php';

/**
 * A TestRunner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class PHPUnit2_TextUI_TestRunner extends PHPUnit2_Runner_BaseTestRunner {
    const SUCCESS_EXIT   = 0;
    const FAILURE_EXIT   = 1;
    const EXCEPTION_EXIT = 2;

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

    /**
     * @param  array $arguments
     * @throws Exception
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
                        PHPUnit2_Util_Fileloader::checkAndLoad(
                          str_replace('_', '/', $option[1]) . '.php'
                        );
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
                            require_once 'PHPUnit2/Util/Skeleton.php';

                            $skeleton = new PHPUnit2_Util_Skeleton($test, $testFile);
                            $skeleton->write();
                        }

                        catch (Exception $e) {
                            print $e->getMessage() . "\n";

                            printf(
                              "Could not write test class skeleton for %s to %s.\n",
                              $test,
                              $test . 'Test.php'
                            );

                            exit(self::FAILURE_EXIT);
                        }

                        printf(
                          "Wrote test class skeleton for %s to %s.\n",
                          $test,
                          $test . 'Test.php'
                        );

                        exit(self::SUCCESS_EXIT);
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

    /**
     * @return PHPUnit2_Framework_TestResult
     * @access protected
     */
    protected function createTestResult() {
        return new PHPUnit2_Framework_TestResult;
    }

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

        if ($testdoxHTMLFile !== FALSE || $testdoxTextFile !== FALSE) {
            require_once 'PHPUnit2/Util/TestDox/ResultPrinter.php';

            if ($testdoxHTMLFile !== FALSE) {
                $result->addListener(
                  PHPUnit2_Util_TestDox_ResultPrinter::factory(
                    'HTML',
                    $testdoxHTMLFile
                  )
                );
            }

            if ($testdoxTextFile !== FALSE) {
                $result->addListener(
                  PHPUnit2_Util_TestDox_ResultPrinter::factory(
                    'Text',
                    $testdoxTextFile
                  )
                );
            }
        }

        if ($xmlLogfile !== FALSE) {
            require_once 'PHPUnit2/Util/Log/XML.php';

            $result->addListener(
              new PHPUnit2_Util_Log_XML($xmlLogfile)
            );
        }

        if ($coverageDataFile !== FALSE ||
            $coverageHTMLFile !== FALSE ||
            $coverageTextFile !== FALSE) {
            $result->collectCodeCoverageInformation(TRUE);
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

    /**
     * Returns the loader to be used.
     *
     * @return PHPUnit2_Runner_TestSuiteLoader
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function getLoader() {
        if ($this->loader === NULL) {
            $this->loader = new PHPUnit2_Runner_StandardTestSuiteLoader;
        }

        return $this->loader;
    }

    /**
     * @param  PHPUnit2_Framework_TestResult $result
     * @param  mixed                         $coverageDataFile
     * @param  mixed                         $coverageHTMLFile
     * @param  mixed                         $coverageTextFile
     * @access protected
     * @since  Method available since Release 2.1.0
     */
    protected function handleCodeCoverageInformation(PHPUnit2_Framework_TestResult $result, $coverageDataFile, $coverageHTMLFile, $coverageTextFile) {
        if ($coverageDataFile !== FALSE &&
            $fp = fopen($coverageDataFile, 'w')) {
            fputs($fp, serialize($result->getCodeCoverageInformation()));
            fclose($fp);
        }

        if ($coverageHTMLFile !== FALSE || $coverageTextFile !== FALSE) {
            require_once 'PHPUnit2/Util/CodeCoverage/Renderer.php';

            if ($coverageHTMLFile !== FALSE) {
                $renderer = PHPUnit2_Util_CodeCoverage_Renderer::factory(
                  'HTML',
                  $result->getCodeCoverageInformation()
                );

                $renderer->renderToFile($coverageHTMLFile);
            }

            if ($coverageTextFile !== FALSE) {
                $renderer = PHPUnit2_Util_CodeCoverage_Renderer::factory(
                  'Text',
                  $result->getCodeCoverageInformation()
                );

                $renderer->renderToFile($coverageTextFile);
            }
        }
    }

    /**
     * @access public
     */
    public function showError($message) {
        self::printVersionString();
        print $message . "\n";

        exit(self::FAILURE_EXIT);
    }

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

        print "  --testdox-html <file>  Write agile documentation in HTML format to file.\n" .
              "  --testdox-text <file>  Write agile documentation in Text format to file.\n" .
              "  --log-xml <file>       Log test progress in XML format to file.\n\n";

        print "  --loader <loader>      TestSuiteLoader implementation to use.\n\n" .
              "  --skeleton             Generate skeleton UnitTest class for Unit in Unit.php.\n\n" .
              "  --wait                 Waits for a keystroke after each test.\n\n" .
              "  --help                 Prints this usage information.\n" .
              "  --version              Prints the version and exits.\n";
    }

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

    /**
     * @param  PHPUnit2_TextUI_ResultPrinter $resultPrinter
     * @access public
     */
    public function setPrinter(PHPUnit2_TextUI_ResultPrinter $resultPrinter) {
        $this->printer = $resultPrinter;
    }

    /**
     * A test started.
     *
     * @param  string  $testName
     * @access public
     */
    public function testStarted($testName) {
    }

    /**
     * A test ended.
     *
     * @param  string  $testName
     * @access public
     */
    public function testEnded($testName) {
    }

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

    /**
     * @access private
     * @since  Method available since Release 2.2.0
     */
    private static function printVersionString() {
        if (!self::$versionStringPrinted) {
            print PHPUnit2_Runner_Version::getVersionString() . "\n\n";
            self::$versionStringPrinted = TRUE;
        }
    }
}

if (PHPUnit2_MAIN_METHOD == 'PHPUnit2_TextUI_TestRunner::main') {
    PHPUnit2_TextUI_TestRunner::main();
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
