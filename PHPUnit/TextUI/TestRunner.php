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
require_once 'PHPUnit/Util/Configuration.php';
require_once 'PHPUnit/Util/PDO.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Report.php';
require_once 'PHPUnit/Util/Timer.php';
require_once 'PHPUnit/Util/Log/CodeCoverage/Database.php';
require_once 'PHPUnit/Util/Log/CodeCoverage/XML.php';
require_once 'PHPUnit/Util/Log/CPD.php';
require_once 'PHPUnit/Util/Log/Database.php';
require_once 'PHPUnit/Util/Log/GraphViz.php';
require_once 'PHPUnit/Util/Log/JSON.php';
require_once 'PHPUnit/Util/Log/Metrics.php';
require_once 'PHPUnit/Util/Log/TAP.php';
require_once 'PHPUnit/Util/Log/PMD.php';
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
     * @access protected
     * @static
     */
    protected static $loader = NULL;

    /**
     * @var    PHPUnit_TextUI_ResultPrinter
     * @access protected
     */
    protected $printer = NULL;

    /**
     * @var    boolean
     * @access protected
     * @static
     */
    protected static $versionStringPrinted = FALSE;

    /**
     * @param  mixed $test
     * @param  array $arguments
     * @throws InvalidArgumentException
     * @access public
     * @static
     */
    public static function run($test, array $arguments = array())
    {
        if ($test instanceof ReflectionClass) {
            $test = new PHPUnit_Framework_TestSuite($test);
        }

        if ($test instanceof PHPUnit_Framework_Test) {
            $aTestRunner = new PHPUnit_TextUI_TestRunner;

            return $aTestRunner->doRun(
              $test,
              $arguments
            );
        } else {
            throw new InvalidArgumentException(
              'No test case or test suite found.'
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
     * @param  array                  $arguments
     * @return PHPUnit_Framework_TestResult
     * @access public
     */
    public function doRun(PHPUnit_Framework_Test $suite, array $arguments = array())
    {
        $this->handleConfiguration($arguments);

        if (is_integer($arguments['repeat'])) {
            $suite = new PHPUnit_Extensions_RepeatedTest(
              $suite,
              $arguments['repeat'],
              $arguments['filter'],
              $arguments['groups'],
              $arguments['excludeGroups']
            );
        }

        $result = $this->createTestResult();

        if ($arguments['stopOnFailure']) {
            $result->stopOnFailure(TRUE);
        }

        if ($this->printer === NULL) {
            if (isset($arguments['printer']) &&
                $arguments['printer'] instanceof PHPUnit_Util_Printer) {
                $this->printer = $arguments['printer'];
            } else {
                $this->printer = new PHPUnit_TextUI_ResultPrinter(
                  NULL, $arguments['verbose']
                );
            }
        }

        $this->printer->write(
          PHPUnit_Runner_Version::getVersionString() . "\n\n"
        );

        foreach ($arguments['listeners'] as $listener) {
            $result->addListener($listener);
        }

        $result->addListener($this->printer);

        if (isset($arguments['testdoxHTMLFile'])) {
            $result->addListener(
              PHPUnit_Util_TestDox_ResultPrinter::factory(
                'HTML',
                $arguments['testdoxHTMLFile']
              )
            );
        }

        if (isset($arguments['testdoxTextFile'])) {
            $result->addListener(
              PHPUnit_Util_TestDox_ResultPrinter::factory(
                'Text',
                $arguments['testdoxTextFile']
              )
            );
        }

        if (isset($arguments['graphvizLogfile'])) {
            if (class_exists('Image_GraphViz', FALSE)) {
                $result->addListener(
                  new PHPUnit_Util_Log_GraphViz($arguments['graphvizLogfile'])
                );
            }
        }

        if ((isset($arguments['coverageXML']) ||
             isset($arguments['metricsXML'])  ||
             isset($arguments['pmdXML'])) &&
             extension_loaded('xdebug')) {
            $result->collectCodeCoverageInformation(TRUE);
        }

        if (isset($arguments['reportDirectory']) &&
            extension_loaded('xdebug')) {
            $result->collectCodeCoverageInformation(TRUE);
        }

        if (isset($arguments['jsonLogfile'])) {
            $result->addListener(
              new PHPUnit_Util_Log_JSON($arguments['jsonLogfile'])
            );
        }

        if (isset($arguments['tapLogfile'])) {
            $result->addListener(
              new PHPUnit_Util_Log_TAP($arguments['tapLogfile'])
            );
        }

        if (isset($arguments['xmlLogfile'])) {
            $result->addListener(
              new PHPUnit_Util_Log_XML(
                $arguments['xmlLogfile'], $arguments['logIncompleteSkipped']
              )
            );
        }

        if (isset($arguments['testDatabaseDSN']) &&
            isset($arguments['testDatabaseLogRevision']) &&
            extension_loaded('pdo')) {
            $writeToTestDatabase = TRUE;
        } else {
            $writeToTestDatabase = FALSE;
        }

        if ($writeToTestDatabase) {
            $dbh = PHPUnit_Util_PDO::factory($arguments['testDatabaseDSN']);

            $dbListener = PHPUnit_Util_Log_Database::getInstance(
              $dbh,
              $arguments['testDatabaseLogRevision'],
              isset($arguments['testDatabaseLogInfo']) ? $arguments['testDatabaseLogInfo'] : ''
            );

            $result->addListener($dbListener);
            $result->collectCodeCoverageInformation(TRUE);
        }

        $suite->run(
          $result,
          $arguments['filter'],
          $arguments['groups'],
          $arguments['excludeGroups']
        );

        $result->flushListeners();

        if ($this->printer instanceof PHPUnit_TextUI_ResultPrinter) {
            $this->printer->printResult($result);
        }

        if (isset($arguments['coverageXML']) &&
            extension_loaded('tokenizer') &&
            extension_loaded('xdebug')) {
            $this->printer->write("\nWriting code coverage data to XML file, this may take a moment.");

            $writer = new PHPUnit_Util_Log_CodeCoverage_XML(
              $arguments['coverageXML']
            );

            $writer->process($result);
            $this->printer->write("\n");
        }

        if ($writeToTestDatabase &&
            extension_loaded('tokenizer') &&
            extension_loaded('xdebug')) {
            $this->printer->write("\nStoring code coverage and software metrics data in database.\nThis may take a moment.");

            $testDb = new PHPUnit_Util_Log_CodeCoverage_Database($dbh);
            $testDb->storeCodeCoverage(
              $result,
              $dbListener->getRunId(),
              $arguments['testDatabaseLogRevision'],
              $arguments['testDatabasePrefix']
            );

            $this->printer->write("\n");
        }

        if (isset($arguments['metricsXML']) &&
            extension_loaded('tokenizer') &&
            extension_loaded('xdebug')) {
            $this->printer->write("\nWriting metrics report XML file, this may take a moment.");

            $writer = new PHPUnit_Util_Log_Metrics(
              $arguments['metricsXML']
            );

            $writer->process($result);
            $this->printer->write("\n");
        }

        if (isset($arguments['pmdXML']) &&
            extension_loaded('tokenizer') &&
            extension_loaded('xdebug')) {
            $writer = new PHPUnit_Util_Log_PMD(
              $arguments['pmdXML'], $arguments['pmd']
            );

            $this->printer->write("\nWriting violations report XML file, this may take a moment.");
            $writer->process($result);

            $writer = new PHPUnit_Util_Log_CPD(
              str_replace('.xml', '-cpd.xml', $arguments['pmdXML'])
            );

            $writer->process(
              $result, $arguments['cpdMinLines'], $arguments['cpdMinMatches']
            );

            $this->printer->write("\n");
        }

        if (isset($arguments['reportDirectory']) &&
            extension_loaded('xdebug')) {
            $this->printer->write("\nGenerating code coverage report, this may take a moment.");

            PHPUnit_Util_Report::render(
              $result,
              $arguments['reportDirectory'],
              $arguments['reportCharset'],
              $arguments['reportYUI'],
              $arguments['reportHighlight'],
              $arguments['reportLowUpperBound'],
              $arguments['reportHighLowerBound']
            );

            $this->printer->write("\n");
        }

        $this->pause($arguments['wait']);

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
        self::write($message);
        exit(self::FAILURE_EXIT);
    }

    /**
     * @param  string $directory
     * @return string
     * @throws RuntimeException
     * @access protected
     * @since  Method available since Release 3.0.0
     */
    protected function getDirectory($directory)
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
     * @param  string $buffer
     * @access protected
     * @since  Method available since Release 3.1.0
     */
    protected static function write($buffer)
    {
        if (php_sapi_name() != 'cli') {
            $buffer = htmlentities($buffer);
        }

        print $buffer;
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
        self::write($message . "\n");

        exit(self::FAILURE_EXIT);
    }


    /**
     * @access public
     * @static
     */
    public static function printVersionString()
    {
        if (!self::$versionStringPrinted) {
            self::write(PHPUnit_Runner_Version::getVersionString() . "\n\n");
            self::$versionStringPrinted = TRUE;
        }
    }

    /**
     * @param  array $arguments
     * @access protected
     * @since  Method available since Release 3.2.1
     */
    protected function handleConfiguration(array &$arguments)
    {
        if (isset($arguments['configuration'])) {
            $arguments['configuration'] = new PHPUnit_Util_Configuration(
              $arguments['configuration']
            );

            $arguments['pmd'] = $arguments['configuration']->getPMDConfiguration();
        } else {
            $arguments['pmd'] = array();
        }

        $arguments['filter']             = isset($arguments['filter'])             ? $arguments['filter']             : FALSE;
        $arguments['listeners']          = isset($arguments['listeners'])          ? $arguments['listeners']          : array();
        $arguments['repeat']             = isset($arguments['repeat'])             ? $arguments['repeat']             : FALSE;
        $arguments['stopOnFailure']      = isset($arguments['stopOnFailure'])      ? $arguments['stopOnFailure']      : FALSE;
        $arguments['testDatabasePrefix'] = isset($arguments['testDatabasePrefix']) ? $arguments['testDatabasePrefix'] : '';
        $arguments['verbose']            = isset($arguments['verbose'])            ? $arguments['verbose']            : FALSE;
        $arguments['wait']               = isset($arguments['wait'])               ? $arguments['wait']               : FALSE;

        if (isset($arguments['configuration'])) {
            $filterConfiguration = $arguments['configuration']->getFilterConfiguration();

            PHPUnit_Util_Filter::$addUncoveredFilesFromWhitelist = $filterConfiguration['whitelist']['addUncoveredFilesFromWhitelist'];

            foreach ($filterConfiguration['blacklist']['include']['directory'] as $dir) {
                PHPUnit_Util_Filter::addDirectoryToFilter(
                  $dir['path'], $dir['suffix']
                );
            }

            foreach ($filterConfiguration['blacklist']['include']['file'] as $file) {
                PHPUnit_Util_Filter::addFileToFilter($file);
            }

            foreach ($filterConfiguration['blacklist']['exclude']['directory'] as $dir) {
                PHPUnit_Util_Filter::removeDirectoryFromFilter(
                  $dir['path'], $dir['suffix']
                );
            }

            foreach ($filterConfiguration['blacklist']['exclude']['file'] as $file) {
                PHPUnit_Util_Filter::removeFileFromFilter($file);
            }

            foreach ($filterConfiguration['whitelist']['include']['directory'] as $dir) {
                PHPUnit_Util_Filter::addDirectoryToWhitelist(
                  $dir['path'], $dir['suffix']
                );
            }

            foreach ($filterConfiguration['whitelist']['include']['file'] as $file) {
                PHPUnit_Util_Filter::addFileToWhitelist($file);
            }

            foreach ($filterConfiguration['whitelist']['exclude']['directory'] as $dir) {
                PHPUnit_Util_Filter::removeDirectoryFromWhitelist(
                  $dir['path'], $dir['suffix']
                );
            }

            foreach ($filterConfiguration['whitelist']['exclude']['file'] as $file) {
                PHPUnit_Util_Filter::removeFileFromWhitelist($file);
            }

            $phpConfiguration = $arguments['configuration']->getPHPConfiguration();

            foreach ($phpConfiguration['ini'] as $name => $value) {
                ini_set($name, $value);
            }

            foreach ($phpConfiguration['var'] as $name => $value) {
                $GLOBALS[$name] = $value;
            }

            $groupConfiguration = $arguments['configuration']->getGroupConfiguration();

            if (!empty($groupConfiguration['include']) && !isset($arguments['groups'])) {
                $arguments['groups'] = $groupConfiguration['include'];
            }

            if (!empty($groupConfiguration['exclude']) && !isset($arguments['excludeGroups'])) {
                $arguments['excludeGroups'] = $groupConfiguration['exclude'];
            }

            $loggingConfiguration = $arguments['configuration']->getLoggingConfiguration();

            if (isset($loggingConfiguration['coverage-html']) && !isset($arguments['reportDirectory'])) {
                if (isset($loggingConfiguration['charset']) && !isset($arguments['reportCharset'])) {
                    $arguments['reportCharset'] = $loggingConfiguration['charset'];
                }

                if (isset($loggingConfiguration['yui']) && !isset($arguments['reportYUI'])) {
                    $arguments['reportYUI'] = $loggingConfiguration['yui'];
                }

                if (isset($loggingConfiguration['highlight']) && !isset($arguments['reportHighlight'])) {
                    $arguments['reportHighlight'] = $loggingConfiguration['highlight'];
                }

                if (isset($loggingConfiguration['lowUpperBound']) && !isset($arguments['reportLowUpperBound'])) {
                    $arguments['reportLowUpperBound'] = $loggingConfiguration['lowUpperBound'];
                }

                if (isset($loggingConfiguration['highLowerBound']) && !isset($arguments['reportHighLowerBound'])) {
                    $arguments['reportHighLowerBound'] = $loggingConfiguration['highLowerBound'];
                }

                $arguments['reportDirectory'] = $loggingConfiguration['coverage-html'];
            }

            if (isset($loggingConfiguration['coverage-xml']) && !isset($arguments['coverageXML'])) {
                $arguments['coverageXML'] = $loggingConfiguration['coverage-xml'];
            }

            if (isset($loggingConfiguration['graphviz']) && !isset($arguments['graphvizLogfile'])) {
                $arguments['graphvizLogfile'] = $loggingConfiguration['graphviz'];
            }

            if (isset($loggingConfiguration['json']) && !isset($arguments['jsonLogfile'])) {
                $arguments['jsonLogfile'] = $loggingConfiguration['json'];
            }

            if (isset($loggingConfiguration['metrics-xml']) && !isset($arguments['metricsXML'])) {
                $arguments['metricsXML'] = $loggingConfiguration['metrics-xml'];
            }

            if (isset($loggingConfiguration['plain'])) {
                $arguments['listeners'][] = new PHPUnit_TextUI_ResultPrinter($loggingConfiguration['plain'], TRUE);
            }

            if (isset($loggingConfiguration['pmd-xml']) && !isset($arguments['pmdXML'])) {
                if (isset($loggingConfiguration['cpdMinLines']) && !isset($arguments['cpdMinLines'])) {
                    $arguments['cpdMinLines'] = $loggingConfiguration['cpdMinLines'];
                }

                if (isset($loggingConfiguration['cpdMinMatches']) && !isset($arguments['cpdMinMatches'])) {
                    $arguments['cpdMinMatches'] = $loggingConfiguration['cpdMinMatches'];
                }

                $arguments['pmdXML'] = $loggingConfiguration['pmd-xml'];
            }

            if (isset($loggingConfiguration['tap']) && !isset($arguments['tapLogfile'])) {
                $arguments['tapLogfile'] = $loggingConfiguration['tap'];
            }

            if (isset($loggingConfiguration['test-xml']) && !isset($arguments['xmlLogfile'])) {
                $arguments['xmlLogfile'] = $loggingConfiguration['test-xml'];

                if (isset($loggingConfiguration['logIncompleteSkipped']) && !isset($arguments['logIncompleteSkipped'])) {
                    $arguments['logIncompleteSkipped'] = $loggingConfiguration['logIncompleteSkipped'];
                }
            }

            if (isset($loggingConfiguration['testdox-html']) && !isset($arguments['testdoxHTMLFile'])) {
                $arguments['testdoxHTMLFile'] = $loggingConfiguration['testdox-html'];
            }

            if (isset($loggingConfiguration['testdox-text']) && !isset($arguments['testdoxTextFile'])) {
                $arguments['testdoxTextFile'] = $loggingConfiguration['testdox-text'];
            }
        }

        $arguments['cpdMinLines']          = isset($arguments['cpdMinLines'])          ? $arguments['cpdMinLines']          : 5;
        $arguments['cpdMinMatches']        = isset($arguments['cpdMinMatches'])        ? $arguments['cpdMinMatches']        : 70;
        $arguments['groups']               = isset($arguments['groups'])               ? $arguments['groups']               : array();
        $arguments['excludeGroups']        = isset($arguments['excludeGroups'])        ? $arguments['excludeGroups']        : array();
        $arguments['logIncompleteSkipped'] = isset($arguments['logIncompleteSkipped']) ? $arguments['logIncompleteSkipped'] : FALSE;
        $arguments['reportCharset']        = isset($arguments['reportCharset'])        ? $arguments['reportCharset']        : 'ISO-8859-1';
        $arguments['reportYUI']            = isset($arguments['reportYUI'])            ? $arguments['reportYUI']            : TRUE;
        $arguments['reportHighlight']      = isset($arguments['reportHighlight'])      ? $arguments['reportHighlight']      : FALSE;
        $arguments['reportLowUpperBound']  = isset($arguments['reportLowUpperBound'])  ? $arguments['reportLowUpperBound']  : 35;
        $arguments['reportHighLowerBound'] = isset($arguments['reportHighLowerBound']) ? $arguments['reportHighLowerBound'] : 70;

        if (isset($arguments['reportDirectory'])) {
            $arguments['reportDirectory'] = $this->getDirectory($arguments['reportDirectory']);
        }
    }
}
?>
