<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHPUnit
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
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

require_once 'PHPUnit2/Framework.php';
require_once 'PHPUnit2/Runner/BaseTestRunner.php';
require_once 'PHPUnit2/Extensions/RepeatedTest.php';
require_once 'PHPUnit2/Runner/StandardTestSuiteLoader.php';
require_once 'PHPUnit2/Runner/Version.php';
require_once 'PHPUnit2/TextUI/ResultPrinter.php';
require_once 'PHPUnit2/Util/TestDox/ResultPrinter.php';
require_once 'PHPUnit2/Util/Fileloader.php';
require_once 'PHPUnit2/Util/Getopt.php';
require_once 'PHPUnit2/Util/Report.php';
require_once 'PHPUnit2/Util/Timer.php';
require_once 'PHPUnit2/Util/Skeleton.php';
require_once 'PHPUnit2/Util/Log/Eclipse.php';
require_once 'PHPUnit2/Util/Log/GraphViz.php';
require_once 'PHPUnit2/Util/Log/TAP.php';
require_once 'PHPUnit2/Util/Log/XML.php';

PHPUnit2_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

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
class PHPUnit2_TextUI_TestRunner extends PHPUnit2_Runner_BaseTestRunner
{
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
    public static function main()
    {
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
     * @throws RuntimeException
     * @access protected
     */
    protected function start(Array $arguments)
    {
        $loaderName = FALSE;
        $parameters = array();
        $repeat     = FALSE;

        $possibleOptions = array(
          'help',
          'loader=',
          'log-eclipse=',
          'log-tap=',
          'log-xml=',
          'printer=',
          'repeat=',
          'report=',
          'skeleton',
          'testdox-html=',
          'testdox-text=',
          'version',
          'wait'
        );

        if (class_exists('Image_GraphViz')) {
            $possibleOptions[] = 'log-graphviz=';
        }

        try {
            $options = PHPUnit2_Util_Getopt::getopt(
              $arguments,
              '',
              $possibleOptions
            );
        }

        catch (RuntimeException $e) {
            $this->showError($e->getMessage());
        }

        $test     = isset($options[1][0]) ? $options[1][0] : FALSE;
        $testFile = isset($options[1][1]) ? $options[1][1] : $test . '.php';

        foreach ($options[0] as $option) {
            switch ($option[0]) {
                case '--help': {
                    $this->showHelp();
                    exit(self::SUCCESS_EXIT);
                }
                break;

                case '--loader': {
                    $loaderName = $option[1];
                }
                break;

                case '--log-eclipse': {
                    $$parameters['eclipseLogfile'] = $option[1];
                }
                break;

                case '--log-graphviz': {
                    $parameters['graphvizDirectory'] = $option[1];
                }
                break;

                case '--log-tap': {
                    $parameters['tapLogfile'] = $option[1];
                }
                break;

                case '--log-xml': {
                    $parameters['xmlLogfile'] = $option[1];
                }
                break;

                case '--printer': {
                    switch (strtolower($option[1])) {
                        case 'tap': {
                            $parameters['printer'] = new PHPUnit2_Util_Log_TAP;
                        }
                        break;
                    }
                }
                break;

                case '--repeat': {
                    $repeat = (int)$option[1];
                }
                break;

                case '--report': {
                    $parameters['reportDirectory'] = $option[1];
                }
                break;

                case '--skeleton': {
                    $this->doSkeleton($test, $testFile);
                }
                break;

                case '--testdox-html': {
                    $parameters['testdoxHTMLFile'] = $option[1];
                }
                break;

                case '--testdox-text': {
                    $parameters['testdoxTextFile'] = $option[1];
                }
                break;

                case '--version': {
                    self::printVersionString();
                    exit(self::SUCCESS_EXIT);
                }
                break;

                case '--wait': {
                    $parameters['wait'] = TRUE;
                }
                break;
            }
        }

        if ($test === FALSE) {
            $this->showHelp();

            exit(self::SUCCESS_EXIT);
        }

        if ($loaderName !== FALSE) {
            $this->handleLoader($loaderName);
        }

        if (!isset($parameters['printer'])) {
            $printer = new PHPUnit2_TextUI_ResultPrinter;
        }

        $test = $this->getTest($test, $testFile);

        if ($repeat !== FALSE) {
            $test = new PHPUnit2_Extensions_RepeatedTest($test, $repeat);
        }

        try {
            return $this->doRun(
              $test,
              $parameters
            );
        }

        catch (Exception $e) {
            throw new RuntimeException(
              'Could not create and run test suite: ' . $e->getMessage()
            );
        }
    }

    /**
     * @param  mixed $test
     * @param  array $parameters
     * @access public
     * @static
     */
    public static function run($test, Array $parameters = array())
    {
        if ($test instanceof ReflectionClass) {
            $test = new PHPUnit2_Framework_TestSuite($test);
        }

        if ($test instanceof PHPUnit2_Framework_Test) {
            $aTestRunner = new PHPUnit2_TextUI_TestRunner;

            return $aTestRunner->doRun(
              $test,
              $parameters
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
    public static function runAndWait(PHPUnit2_Framework_Test $suite)
    {
        $aTestRunner = new PHPUnit2_TextUI_TestRunner;

        $aTestRunner->doRun(
          $suite,
          array(
            'wait' => TRUE
          )
        );

    }

    /**
     * @return PHPUnit2_Framework_TestResult
     * @access protected
     */
    protected function createTestResult()
    {
        return new PHPUnit2_Framework_TestResult;
    }

    /**
     * @param  PHPUnit2_Framework_Test $suite
     * @param  array                   $parameters
     * @return PHPUnit2_Framework_TestResult
     * @access public
     */
    public function doRun(PHPUnit2_Framework_Test $suite, Array $parameters = array())
    {
        if (isset($parameters['graphvizDirectory'])) {
            $parameters['graphvizDirectory'] = $this->getDirectory($parameters['graphvizDirectory']);
        }

        if (isset($parameters['reportDirectory'])) {
            $parameters['reportDirectory'] = $this->getDirectory($parameters['reportDirectory']);
        }

        $result = $this->createTestResult();

        if ($this->printer === NULL) {
            if (isset($parameters['printer']) && $parameters instanceof PHPUnit2_Util_Printer) {
                $this->printer = $parameters['printer'];
            } else {
                $this->printer = new PHPUnit2_TextUI_ResultPrinter;
            }
        }

        $this->printer->write(
          PHPUnit2_Runner_Version::getVersionString() . "\n\n"
        );

        $result->addListener($this->printer);

        if (isset($parameters['testdoxHTMLFile'])) {
            $result->addListener(
              PHPUnit2_Util_TestDox_ResultPrinter::factory(
                'HTML',
                $parameters['testdoxHTMLFile']
              )
            );
        }

        if (isset($parameters['testdoxTextFile'])) {
            $result->addListener(
              PHPUnit2_Util_TestDox_ResultPrinter::factory(
                'Text',
                $parameters['testdoxTextFile']
              )
            );
        }

        if (isset($parameters['graphvizDirectory'])) {
            $result->addListener(
              new PHPUnit2_Util_Log_GraphViz($parameters['graphvizDirectory'])
            );
        }

        if (isset($parameters['reportDirectory'])) {
            $result->addListener(
              new PHPUnit2_Util_Log_GraphViz($parameters['reportDirectory'])
            );

            $result->collectCodeCoverageInformation(TRUE);
        }

        if (isset($parameters['eclipseLogfile'])) {
            $result->addListener(
              new PHPUnit2_Util_Log_Eclipse($parameters['eclipseLogfile'])
            );
        }

        if (isset($parameters['tapLogfile'])) {
            $result->addListener(
              new PHPUnit2_Util_Log_TAP($parameters['tapLogfile'])
            );
        }

        if (isset($parameters['xmlLogfile'])) {
            $result->addListener(
              new PHPUnit2_Util_Log_XML($parameters['xmlLogfile'])
            );
        }

        PHPUnit2_Util_Timer::start();
        $suite->run($result);
        $timeElapsed = PHPUnit2_Util_Timer::stop();

        $result->flushListeners();

        if (isset($parameters['reportDirectory'])) {
            PHPUnit2_Util_Report::render($result, $parameters['reportDirectory']);
        }

        $this->pause($parameters['wait']);

        if ($this->printer instanceof PHPUnit2_TextUI_ResultPrinter) {
            $this->printer->printResult($result, $timeElapsed);
        }

        return $result;
    }

    /**
     * Returns the loader to be used.
     *
     * @return PHPUnit2_Runner_TestSuiteLoader
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function getLoader()
    {
        if ($this->loader === NULL) {
            $this->loader = new PHPUnit2_Runner_StandardTestSuiteLoader;
        }

        return $this->loader;
    }

    /**
     * @access public
     */
    public function showError($message)
    {
        self::printVersionString();
        print $message . "\n";

        exit(self::FAILURE_EXIT);
    }

    /**
     * @access public
     */
    public function showHelp()
    {
        self::printVersionString();
        print "Usage: phpunit [switches] UnitTest [UnitTest.php]\n\n";

        if (class_exists('Image_GraphViz')) {
            print "  --log-graphviz <dir>   Log test execution in GraphViz markup.\n";
        }

        print "  --log-tap <file>       Log test execution in TAP format to file.\n" .
              "  --log-xml <file>       Log test execution in XML format to file.\n\n";

        if (extension_loaded('xdebug')) {
            print "  --report <dir>         Generate combined test/coverage report in HTML format.\n";
        } else {
            print "  --report <dir>         Generate test report in HTML format.\n";
        }

        print "  --testdox-html <file>  Write agile documentation in HTML format to file.\n" .
              "  --testdox-text <file>  Write agile documentation in Text format to file.\n\n" .
              "  --printer {dots|tap}   Report test execution progress in DOTS or TAP format.\n" .
              "  --loader <loader>      TestSuiteLoader implementation to use.\n\n" .
              "  --skeleton             Generate skeleton UnitTest class for Unit in Unit.php.\n\n" .
              "  --repeat <times>       Runs the test(s) repeatedly.\n" .
              "  --wait                 Waits for a keystroke after each test.\n\n" .
              "  --help                 Prints this usage information.\n" .
              "  --version              Prints the version and exits.\n";
    }

    /**
     * @param  boolean $wait
     * @access protected
     */
    protected function pause($wait)
    {
        if (!$wait) {
            return;
        }

        if ($this->printer instanceof PHPUnit2_TextUI_ResultPrinter) {
            $this->printer->printWaitPrompt();
        }

        fgets(STDIN);
    }

    /**
     * @param  PHPUnit2_TextUI_ResultPrinter $resultPrinter
     * @access public
     */
    public function setPrinter(PHPUnit2_TextUI_ResultPrinter $resultPrinter)
    {
        $this->printer = $resultPrinter;
    }

    /**
     * A test started.
     *
     * @param  string  $testName
     * @access public
     */
    public function testStarted($testName)
    {
    }

    /**
     * A test ended.
     *
     * @param  string  $testName
     * @access public
     */
    public function testEnded($testName)
    {
    }

    /**
     * A test failed.
     *
     * @param  integer                                 $status
     * @param  PHPUnit2_Framework_Test                 $test
     * @param  PHPUnit2_Framework_AssertionFailedError $e
     * @access public
     */
    public function testFailed($status, PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)
    {
    }

    /**
     * Override to define how to handle a failed loading of
     * a test suite.
     *
     * @param  string  $message
     * @access protected
     */
    protected function runFailed($message)
    {
        self::printVersionString();
        print $message;
        exit(self::FAILURE_EXIT);
    }

    /**
     * @param  string  $test
     * @param  string  $testFile
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function doSkeleton($test, $testFile)
    {
        if ($test !== FALSE) {
            self::printVersionString();

            try {
                $skeleton = new PHPUnit2_Util_Skeleton($test, $testFile);
                $skeleton->write();
            }

            catch (Exception $e) {
                print $e->getMessage() . "\n";

                printf(
                  "Could not write test class skeleton for %s to %s.\n",
                  $test,
                  $testFile
                );

                exit(self::FAILURE_EXIT);
            }

            printf(
              "Wrote test class skeleton for %s to %s.\n",
              $test,
              $skeleton->getTestSourceFile()
            );

            exit(self::SUCCESS_EXIT);
        }
    }

    /**
     * @param  string  $loaderName
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function handleLoader($loaderName)
    {
        if (!class_exists($loaderName)) {
            PHPUnit2_Util_Fileloader::checkAndLoad(
              str_replace('_', '/', $loaderName) . '.php'
            );
        }

        if (class_exists($loaderName)) {
            $class = new ReflectionClass($loaderName);

            if ($class->implementsInterface('PHPUnit2_Runner_TestSuiteLoader') &&
                $class->isInstantiable()) {
                $this->loader = $class->newInstance();
            }
        }

        if ($this->loader === NULL) {
            $this->showError(
              sprintf(
                'Could not use "%s" as loader.',

                $loaderName
              )
            );
        }
    }

    /**
     * @access private
     * @since  Method available since Release 2.2.0
     */
    private static function printVersionString()
    {
        if (!self::$versionStringPrinted) {
            print PHPUnit2_Runner_Version::getVersionString() . "\n\n";
            self::$versionStringPrinted = TRUE;
        }
    }

    /**
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function getDirectory($directory)
    {
        if (substr($directory, -1, 1) != DIRECTORY_SEPARATOR) {
            $directory .= DIRECTORY_SEPARATOR;
        }

        return $directory;
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
