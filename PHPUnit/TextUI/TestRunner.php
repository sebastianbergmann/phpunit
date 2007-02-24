<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Runner/BaseTestRunner.php';
require_once 'PHPUnit/Extensions/RepeatedTest.php';
require_once 'PHPUnit/Runner/StandardTestSuiteLoader.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/ResultPrinter.php';
require_once 'PHPUnit/Util/TestDox/ResultPrinter.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Report.php';
require_once 'PHPUnit/Util/Report/GraphViz.php';
require_once 'PHPUnit/Util/Timer.php';
require_once 'PHPUnit/Util/Log/GraphViz.php';
require_once 'PHPUnit/Util/Log/JSON.php';
require_once 'PHPUnit/Util/Log/TAP.php';
require_once 'PHPUnit/Util/Log/XML.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestRunner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_TextUI_TestRunner extends PHPUnit_Runner_BaseTestRunner
{
    const SUCCESS_EXIT   = 0;
    const FAILURE_EXIT   = 1;
    const EXCEPTION_EXIT = 2;

    /**
     * @var    PHPUnit_Runner_TestSuiteLoader
     * @access private
     * @static
     */
    private static $loader = NULL;

    /**
     * @var    PHPUnit_TextUI_ResultPrinter
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
     * @param  mixed $test
     * @param  array $parameters
     * @access public
     * @static
     */
    public static function run($test, array $parameters = array())
    {
        if ($test instanceof ReflectionClass) {
            $test = new PHPUnit_Framework_TestSuite($test);
        }

        if ($test instanceof PHPUnit_Framework_Test) {
            $aTestRunner = new PHPUnit_TextUI_TestRunner;

            return $aTestRunner->doRun(
              $test,
              $parameters
            );
        }
    }

    /**
     * Runs a single test and waits until the user types RETURN.
     *
     * @param  PHPUnit_Framework_Test $suite
     * @access public
     * @static
     */
    public static function runAndWait(PHPUnit_Framework_Test $suite)
    {
        $aTestRunner = new PHPUnit_TextUI_TestRunner;

        $aTestRunner->doRun(
          $suite,
          array(
            'wait' => TRUE
          )
        );

    }

    /**
     * @return PHPUnit_Framework_TestResult
     * @access protected
     */
    protected function createTestResult()
    {
        return new PHPUnit_Framework_TestResult;
    }

    /**
     * @param  PHPUnit_Framework_Test $suite
     * @param  array                   $parameters
     * @return PHPUnit_Framework_TestResult
     * @access public
     */
    public function doRun(PHPUnit_Framework_Test $suite, array $parameters = array())
    {
        $parameters['repeat']  = isset($parameters['repeat'])  ? $parameters['repeat']  : FALSE;
        $parameters['filter']  = isset($parameters['filter'])  ? $parameters['filter']  : FALSE;
        $parameters['verbose'] = isset($parameters['verbose']) ? $parameters['verbose'] : FALSE;
        $parameters['wait']    = isset($parameters['wait'])    ? $parameters['wait']    : FALSE;

        if (is_integer($parameters['repeat'])) {
            $suite = new PHPUnit_Extensions_RepeatedTest($suite, $parameters['repeat']);
        }

        if (isset($parameters['reportDirectory'])) {
            $parameters['reportDirectory'] = $this->getDirectory($parameters['reportDirectory']);
        }

        $result = $this->createTestResult();

        if ($this->printer === NULL) {
            if (isset($parameters['printer']) && $parameters['printer'] instanceof PHPUnit_Util_Printer) {
                $this->printer = $parameters['printer'];
            } else {
                $this->printer = new PHPUnit_TextUI_ResultPrinter(NULL, $parameters['verbose']);
            }
        }

        $this->printer->write(
          PHPUnit_Runner_Version::getVersionString() . "\n\n"
        );

        $result->addListener($this->printer);

        if (isset($parameters['testdoxHTMLFile'])) {
            $result->addListener(
              PHPUnit_Util_TestDox_ResultPrinter::factory(
                'HTML',
                $parameters['testdoxHTMLFile']
              )
            );
        }

        if (isset($parameters['testdoxTextFile'])) {
            $result->addListener(
              PHPUnit_Util_TestDox_ResultPrinter::factory(
                'Text',
                $parameters['testdoxTextFile']
              )
            );
        }

        if (isset($parameters['graphvizLogfile'])) {
            if (class_exists('Image_GraphViz', FALSE)) {
                $result->addListener(
                  new PHPUnit_Util_Log_GraphViz($parameters['graphvizLogfile'])
                );
            }
        }

        if (isset($parameters['reportDirectory']) &&
            extension_loaded('xdebug')) {
            if (class_exists('Image_GraphViz', FALSE)) {
                $result->addListener(
                  new PHPUnit_Util_Report_GraphViz($parameters['reportDirectory'])
                );
            }

            $result->collectCodeCoverageInformation(TRUE);
        }

        if (isset($parameters['jsonLogfile'])) {
            $result->addListener(
              new PHPUnit_Util_Log_JSON($parameters['jsonLogfile'])
            );
        }

        if (isset($parameters['tapLogfile'])) {
            $result->addListener(
              new PHPUnit_Util_Log_TAP($parameters['tapLogfile'])
            );
        }

        if (isset($parameters['xmlLogfile'])) {
            $result->addListener(
              new PHPUnit_Util_Log_XML($parameters['xmlLogfile'])
            );
        }

        $suite->run($result, $parameters['filter']);

        $result->flushListeners();

        if ($this->printer instanceof PHPUnit_TextUI_ResultPrinter) {
            $this->printer->printResult($result);
        }

        if (isset($parameters['reportDirectory']) &&
            extension_loaded('xdebug')) {
            $this->printer->write("\nGenerating report, this may take a moment.");
            PHPUnit_Util_Report::render($result, $parameters['reportDirectory']);
            $this->printer->write("\n");
        }

        $this->pause($parameters['wait']);

        return $result;
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

        if ($this->printer instanceof PHPUnit_TextUI_ResultPrinter) {
            $this->printer->printWaitPrompt();
        }

        fgets(STDIN);
    }

    /**
     * @param  PHPUnit_TextUI_ResultPrinter $resultPrinter
     * @access public
     */
    public function setPrinter(PHPUnit_TextUI_ResultPrinter $resultPrinter)
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
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @access public
     */
    public function testFailed($status, PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e)
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
     * @param  string $directory
     * @return string
     * @throws RuntimeException
     * @access private
     * @since  Method available since Release 3.0.0
     */
    private function getDirectory($directory)
    {
        if (substr($directory, -1, 1) != DIRECTORY_SEPARATOR) {
            $directory .= DIRECTORY_SEPARATOR;
        }

        if (is_dir($directory) || mkdir($directory, 0777, TRUE)) {
            return $directory;
        } else {
            throw new RuntimeException(
              sprintf(
                'Directory "%s" does not exist.',
                $directory
              )
            );
        }
    }

    /**
     * Returns the loader to be used.
     *
     * @return PHPUnit_Runner_TestSuiteLoader
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function getLoader()
    {
        if (self::$loader === NULL) {
            self::$loader = new PHPUnit_Runner_StandardTestSuiteLoader;
        }

        return self::$loader;
    }

    /**
     * Sets the loader to be used.
     *
     * @param PHPUnit_Runner_TestSuiteLoader $loader
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function setLoader(PHPUnit_Runner_TestSuiteLoader $loader)
    {
        self::$loader = $loader;
    }

    /**
     * @access public
     */
    public static function showError($message)
    {
        self::printVersionString();
        print $message . "\n";

        exit(self::FAILURE_EXIT);
    }


    /**
     * @access public
     * @static
     */
    public static function printVersionString()
    {
        if (!self::$versionStringPrinted) {
            print PHPUnit_Runner_Version::getVersionString() . "\n\n";
            self::$versionStringPrinted = TRUE;
        }
    }
}
?>
