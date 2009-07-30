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
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Runner/BaseTestRunner.php';
require_once 'PHPUnit/Extensions/RepeatedTest.php';
require_once 'PHPUnit/Runner/StandardTestSuiteLoader.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/ResultPrinter.php';
require_once 'PHPUnit/Util/Configuration.php';
require_once 'PHPUnit/Util/PDO.php';
require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Report.php';
require_once 'PHPUnit/Util/Timer.php';

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
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_TextUI_TestRunner extends PHPUnit_Runner_BaseTestRunner
{
    const SUCCESS_EXIT   = 0;
    const FAILURE_EXIT   = 1;
    const EXCEPTION_EXIT = 2;

    /**
     * @var    PHPUnit_Runner_TestSuiteLoader
     */
    protected $loader = NULL;

    /**
     * @var    PHPUnit_TextUI_ResultPrinter
     */
    protected $printer = NULL;

    /**
     * @var    boolean
     */
    protected static $versionStringPrinted = FALSE;

    /**
     * @param  PHPUnit_Runner_TestSuiteLoader $loader
     * @since  Method available since Release 3.4.0
     */
    public function __construct(PHPUnit_Runner_TestSuiteLoader $loader = NULL)
    {
        $this->loader = $loader;
    }

    /**
     * @param  mixed $test
     * @param  array $arguments
     * @throws InvalidArgumentException
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
     */
    protected function createTestResult()
    {
        return new PHPUnit_Framework_TestResult;
    }

    /**
     * @param  PHPUnit_Framework_Test $suite
     * @param  array                  $arguments
     * @return PHPUnit_Framework_TestResult
     */
    public function doRun(PHPUnit_Framework_Test $suite, array $arguments = array())
    {
        $this->handleConfiguration($arguments);

        if (isset($arguments['bootstrap'])) {
            $bootstrap = PHPUnit_Util_Fileloader::load($arguments['bootstrap']);

            if ($bootstrap) {
                $GLOBALS['__PHPUNIT_BOOTSTRAP'] = $bootstrap;
            }
        }

        if ($arguments['backupGlobals'] === FALSE) {
            $suite->setBackupGlobals(FALSE);
        }

        if ($arguments['backupStaticAttributes'] === FALSE) {
            $suite->setBackupStaticAttributes(FALSE);
        }

        if (is_integer($arguments['repeat'])) {
            $suite = new PHPUnit_Extensions_RepeatedTest(
              $suite,
              $arguments['repeat'],
              $arguments['filter'],
              $arguments['groups'],
              $arguments['excludeGroups'],
              $arguments['processIsolation']
            );
        }

        $result = $this->createTestResult();

        if (!$arguments['convertErrorsToExceptions']) {
            $result->convertErrorsToExceptions(FALSE);
        }

        if (!$arguments['convertNoticesToExceptions']) {
            PHPUnit_Framework_Error_Notice::$enabled = FALSE;
        }

        if (!$arguments['convertWarningsToExceptions']) {
            PHPUnit_Framework_Error_Warning::$enabled = FALSE;
        }

        if ($arguments['stopOnFailure']) {
            $result->stopOnFailure(TRUE);
        }

        if ($this->printer === NULL) {
            if (isset($arguments['printer']) &&
                $arguments['printer'] instanceof PHPUnit_Util_Printer) {
                $this->printer = $arguments['printer'];
            } else {
                $this->printer = new PHPUnit_TextUI_ResultPrinter(
                  NULL, $arguments['verbose'], $arguments['colors'], $arguments['debug']
                );
            }
        }

        if (!$this->printer instanceof PHPUnit_Util_Log_TAP &&
            !self::$versionStringPrinted) {
            $this->printer->write(
              PHPUnit_Runner_Version::getVersionString() . "\n\n"
            );
        }

        foreach ($arguments['listeners'] as $listener) {
            $result->addListener($listener);
        }

        $result->addListener($this->printer);

        if (isset($arguments['storyHTMLFile'])) {
            require_once 'PHPUnit/Extensions/Story/ResultPrinter/HTML.php';

            $result->addListener(
              new PHPUnit_Extensions_Story_ResultPrinter_HTML(
                $arguments['storyHTMLFile']
              )
            );
        }

        if (isset($arguments['storyTextFile'])) {
            require_once 'PHPUnit/Extensions/Story/ResultPrinter/Text.php';

            $result->addListener(
              new PHPUnit_Extensions_Story_ResultPrinter_Text(
                $arguments['storyTextFile']
              )
            );
        }

        if (isset($arguments['testdoxHTMLFile'])) {
            require_once 'PHPUnit/Util/TestDox/ResultPrinter/HTML.php';

            $result->addListener(
              new PHPUnit_Util_TestDox_ResultPrinter_HTML(
                $arguments['testdoxHTMLFile']
              )
            );
        }

        if (isset($arguments['testdoxTextFile'])) {
            require_once 'PHPUnit/Util/TestDox/ResultPrinter/Text.php';

            $result->addListener(
              new PHPUnit_Util_TestDox_ResultPrinter_Text(
                $arguments['testdoxTextFile']
              )
            );
        }

        if (isset($arguments['graphvizLogfile'])) {
            if (PHPUnit_Util_Filesystem::fileExistsInIncludePath('Image/GraphViz.php')) {
                require_once 'PHPUnit/Util/Log/GraphViz.php';

                $result->addListener(
                  new PHPUnit_Util_Log_GraphViz($arguments['graphvizLogfile'])
                );
            }
        }

        if ((isset($arguments['coverageClover']) ||
             isset($arguments['coverageSource']) ||
             isset($arguments['metricsXML']) ||
             isset($arguments['pmdXML']) ||
             isset($arguments['reportDirectory'])) &&
             extension_loaded('xdebug')) {
            $result->collectCodeCoverageInformation(TRUE);
        }

        if (isset($arguments['jsonLogfile'])) {
            require_once 'PHPUnit/Util/Log/JSON.php';

            $result->addListener(
              new PHPUnit_Util_Log_JSON($arguments['jsonLogfile'])
            );
        }

        if (isset($arguments['tapLogfile'])) {
            require_once 'PHPUnit/Util/Log/TAP.php';

            $result->addListener(
              new PHPUnit_Util_Log_TAP($arguments['tapLogfile'])
            );
        }

        if (isset($arguments['junitLogfile'])) {
            require_once 'PHPUnit/Util/Log/JUnit.php';

            $result->addListener(
              new PHPUnit_Util_Log_JUnit(
                $arguments['junitLogfile'], $arguments['logIncompleteSkipped']
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

            require_once 'PHPUnit/Util/Log/Database.php';

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
          $arguments['excludeGroups'],
          $arguments['processIsolation']
        );

        unset($suite);
        $result->flushListeners();

        if ($this->printer instanceof PHPUnit_TextUI_ResultPrinter) {
            $this->printer->printResult($result);
        }

        if (extension_loaded('tokenizer') && extension_loaded('xdebug')) {
            if (isset($arguments['coverageClover'])) {
                $this->printer->write("\nWriting code coverage data to XML file, this may take a moment.");

                require_once 'PHPUnit/Util/Log/CodeCoverage/XML/Clover.php';

                $writer = new PHPUnit_Util_Log_CodeCoverage_XML_Clover(
                  $arguments['coverageClover']
                );

                $writer->process($result);
                $this->printer->write("\n");
            }

            if (isset($arguments['coverageSource'])) {
                $this->printer->write("\nWriting code coverage data to XML files, this may take a moment.");

                require_once 'PHPUnit/Util/Log/CodeCoverage/XML/Source.php';

                $writer = new PHPUnit_Util_Log_CodeCoverage_XML_Source(
                  $arguments['coverageSource']
                );

                $writer->process($result);
                $this->printer->write("\n");
            }

            if ($writeToTestDatabase) {
                $this->printer->write("\nStoring code coverage and software metrics data in database.\nThis may take a moment.");

                require_once 'PHPUnit/Util/Log/CodeCoverage/Database.php';

                $testDb = new PHPUnit_Util_Log_CodeCoverage_Database($dbh);
                $testDb->storeCodeCoverage(
                  $result,
                  $dbListener->getRunId(),
                  $arguments['testDatabaseLogRevision'],
                  $arguments['testDatabasePrefix']
                );

                $this->printer->write("\n");
            }

            if (isset($arguments['metricsXML'])) {
                $this->printer->write("\nWriting metrics report XML file, this may take a moment.");

                require_once 'PHPUnit/Util/Log/Metrics.php';

                $writer = new PHPUnit_Util_Log_Metrics(
                  $arguments['metricsXML']
                );

                $writer->process($result);
                $this->printer->write("\n");
            }

            if (isset($arguments['pmdXML'])) {
                require_once 'PHPUnit/Util/Log/PMD.php';

                $writer = new PHPUnit_Util_Log_PMD(
                  $arguments['pmdXML'], $arguments['pmd']
                );

                $this->printer->write("\nWriting violations report XML file, this may take a moment.");
                $writer->process($result);

                require_once 'PHPUnit/Util/Log/CPD.php';

                $writer = new PHPUnit_Util_Log_CPD(
                  str_replace('.xml', '-cpd.xml', $arguments['pmdXML'])
                );

                $writer->process(
                  $result, $arguments['cpdMinLines'], $arguments['cpdMinMatches']
                );

                $this->printer->write("\n");
            }

            if (isset($arguments['reportDirectory'])) {
                $this->printer->write("\nGenerating code coverage report, this may take a moment.");

                $title = '';

                if (isset($arguments['configuration'])) {
                    $loggingConfiguration = $arguments['configuration']->getLoggingConfiguration();

                    if (isset($loggingConfiguration['title'])) {
                        $title = $loggingConfiguration['title'];
                    }
                }

                PHPUnit_Util_Report::render(
                  $result,
                  $arguments['reportDirectory'],
                  $title,
                  $arguments['reportCharset'],
                  $arguments['reportYUI'],
                  $arguments['reportHighlight'],
                  $arguments['reportLowUpperBound'],
                  $arguments['reportHighLowerBound']
                );

                $this->printer->write("\n");
            }
        }

        $this->pause($arguments['wait']);

        return $result;
    }

    /**
     * @param  boolean $wait
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
     */
    public function setPrinter(PHPUnit_TextUI_ResultPrinter $resultPrinter)
    {
        $this->printer = $resultPrinter;
    }

    /**
     * Override to define how to handle a failed loading of
     * a test suite.
     *
     * @param  string  $message
     */
    protected function runFailed($message)
    {
        self::printVersionString();
        self::write($message);
        exit(self::FAILURE_EXIT);
    }

    /**
     * @param  string $buffer
     * @since  Method available since Release 3.1.0
     */
    protected static function write($buffer)
    {
        if (PHP_SAPI != 'cli') {
            $buffer = htmlspecialchars($buffer);
        }

        print $buffer;
    }

    /**
     * Returns the loader to be used.
     *
     * @return PHPUnit_Runner_TestSuiteLoader
     * @since  Method available since Release 2.2.0
     */
    public function getLoader()
    {
        if ($this->loader === NULL) {
            $this->loader = new PHPUnit_Runner_StandardTestSuiteLoader;
        }

        return $this->loader;
    }

    /**
     */
    public static function showError($message)
    {
        self::printVersionString();
        self::write($message . "\n");

        exit(self::FAILURE_EXIT);
    }

    /**
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
     * @since  Method available since Release 3.2.1
     */
    protected function handleConfiguration(array &$arguments)
    {
        if (isset($arguments['configuration']) &&
            !$arguments['configuration'] instanceof PHPUnit_Util_Configuration) {
            $arguments['configuration'] = PHPUnit_Util_Configuration::getInstance(
              $arguments['configuration']
            );

            $arguments['pmd'] = $arguments['configuration']->getPMDConfiguration();
        } else {
            $arguments['pmd'] = array();
        }

        $arguments['debug']              = isset($arguments['debug'])              ? $arguments['debug']              : FALSE;
        $arguments['filter']             = isset($arguments['filter'])             ? $arguments['filter']             : FALSE;
        $arguments['listeners']          = isset($arguments['listeners'])          ? $arguments['listeners']          : array();
        $arguments['repeat']             = isset($arguments['repeat'])             ? $arguments['repeat']             : FALSE;
        $arguments['testDatabasePrefix'] = isset($arguments['testDatabasePrefix']) ? $arguments['testDatabasePrefix'] : '';
        $arguments['verbose']            = isset($arguments['verbose'])            ? $arguments['verbose']            : FALSE;
        $arguments['wait']               = isset($arguments['wait'])               ? $arguments['wait']               : FALSE;

        if (isset($arguments['configuration'])) {
            $arguments['configuration']->handlePHPConfiguration();

            $filterConfiguration = $arguments['configuration']->getFilterConfiguration();

            PHPUnit_Util_Filter::$addUncoveredFilesFromWhitelist = $filterConfiguration['whitelist']['addUncoveredFilesFromWhitelist'];

            foreach ($filterConfiguration['blacklist']['include']['directory'] as $dir) {
                PHPUnit_Util_Filter::addDirectoryToFilter(
                  $dir['path'], $dir['suffix'], $dir['group'], $dir['prefix']
                );
            }

            foreach ($filterConfiguration['blacklist']['include']['file'] as $file) {
                PHPUnit_Util_Filter::addFileToFilter($file);
            }

            foreach ($filterConfiguration['blacklist']['exclude']['directory'] as $dir) {
                PHPUnit_Util_Filter::removeDirectoryFromFilter(
                  $dir['path'], $dir['suffix'], $dir['group'], $dir['prefix']
                );
            }

            foreach ($filterConfiguration['blacklist']['exclude']['file'] as $file) {
                PHPUnit_Util_Filter::removeFileFromFilter($file);
            }

            foreach ($filterConfiguration['whitelist']['include']['directory'] as $dir) {
                PHPUnit_Util_Filter::addDirectoryToWhitelist(
                  $dir['path'], $dir['suffix'], $dir['prefix']
                );
            }

            foreach ($filterConfiguration['whitelist']['include']['file'] as $file) {
                PHPUnit_Util_Filter::addFileToWhitelist($file);
            }

            foreach ($filterConfiguration['whitelist']['exclude']['directory'] as $dir) {
                PHPUnit_Util_Filter::removeDirectoryFromWhitelist(
                  $dir['path'], $dir['suffix'], $dir['prefix']
                );
            }

            foreach ($filterConfiguration['whitelist']['exclude']['file'] as $file) {
                PHPUnit_Util_Filter::removeFileFromWhitelist($file);
            }

            $phpunitConfiguration = $arguments['configuration']->getPHPUnitConfiguration();

            if (isset($phpunitConfiguration['backupGlobals']) && !isset($arguments['backupGlobals'])) {
                $arguments['backupGlobals'] = $phpunitConfiguration['backupGlobals'];
            }

            if (isset($phpunitConfiguration['backupStaticAttributes']) && !isset($arguments['backupStaticAttributes'])) {
                $arguments['backupStaticAttributes'] = $phpunitConfiguration['backupStaticAttributes'];
            }

            if (isset($phpunitConfiguration['bootstrap']) && !isset($arguments['bootstrap'])) {
                $arguments['bootstrap'] = $phpunitConfiguration['bootstrap'];
            }

            if (isset($phpunitConfiguration['colors']) && !isset($arguments['colors'])) {
                $arguments['colors'] = $phpunitConfiguration['colors'];
            }

            if (isset($phpunitConfiguration['convertErrorsToExceptions']) && !isset($arguments['convertErrorsToExceptions'])) {
                $arguments['convertErrorsToExceptions'] = $phpunitConfiguration['convertErrorsToExceptions'];
            }

            if (isset($phpunitConfiguration['convertNoticesToExceptions']) && !isset($arguments['convertNoticesToExceptions'])) {
                $arguments['convertNoticesToExceptions'] = $phpunitConfiguration['convertNoticesToExceptions'];
            }

            if (isset($phpunitConfiguration['convertWarningsToExceptions']) && !isset($arguments['convertWarningsToExceptions'])) {
                $arguments['convertWarningsToExceptions'] = $phpunitConfiguration['convertWarningsToExceptions'];
            }

            if (isset($phpunitConfiguration['processIsolation']) && !isset($arguments['processIsolation'])) {
                $arguments['processIsolation'] = $phpunitConfiguration['processIsolation'];
            }

            if (isset($phpunitConfiguration['stopOnFailure']) && !isset($arguments['stopOnFailure'])) {
                $arguments['stopOnFailure'] = $phpunitConfiguration['stopOnFailure'];
            }

            $groupConfiguration = $arguments['configuration']->getGroupConfiguration();

            if (!empty($groupConfiguration['include']) && !isset($arguments['groups'])) {
                $arguments['groups'] = $groupConfiguration['include'];
            }

            if (!empty($groupConfiguration['exclude']) && !isset($arguments['excludeGroups'])) {
                $arguments['excludeGroups'] = $groupConfiguration['exclude'];
            }

            foreach ($arguments['configuration']->getListenerConfiguration() as $listener) {
                if (!class_exists($listener['class'], FALSE) && $listener['file'] !== '') {
                    $file = PHPUnit_Util_Filesystem::fileExistsInIncludePath(
                      $listener['file']
                    );

                    if ($file !== FALSE) {
                        require $file;
                    }
                }

                if (class_exists($listener['class'], FALSE)) {
                    if (count($listener['arguments']) == 0) {
                        $listener = new $listener['class'];
                    } else {
                        $listenerClass = new ReflectionClass($listener['class']);
                        $listener      = $listenerClass->newInstanceArgs($listener['arguments']);
                    }

                    if ($listener instanceof PHPUnit_Framework_TestListener) {
                        $arguments['listeners'][] = $listener;
                    }
                }
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

            if (isset($loggingConfiguration['coverage-clover']) && !isset($arguments['coverageClover'])) {
                $arguments['coverageClover'] = $loggingConfiguration['coverage-clover'];
            }

            if (isset($loggingConfiguration['coverage-xml']) && !isset($arguments['coverageClover'])) {
                $arguments['coverageClover'] = $loggingConfiguration['coverage-xml'];
            }

            if (isset($loggingConfiguration['coverage-source']) && !isset($arguments['coverageSource'])) {
                $arguments['coverageSource'] = $loggingConfiguration['coverage-source'];
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

            if (isset($loggingConfiguration['junit']) && !isset($arguments['junitLogfile'])) {
                $arguments['junitLogfile'] = $loggingConfiguration['junit'];

                if (isset($loggingConfiguration['logIncompleteSkipped']) && !isset($arguments['logIncompleteSkipped'])) {
                    $arguments['logIncompleteSkipped'] = $loggingConfiguration['logIncompleteSkipped'];
                }
            }

            if (isset($loggingConfiguration['story-html']) && !isset($arguments['storyHTMLFile'])) {
                $arguments['storyHTMLFile'] = $loggingConfiguration['story-html'];
            }

            if (isset($loggingConfiguration['story-text']) && !isset($arguments['storyTextFile'])) {
                $arguments['storsTextFile'] = $loggingConfiguration['story-text'];
            }

            if (isset($loggingConfiguration['testdox-html']) && !isset($arguments['testdoxHTMLFile'])) {
                $arguments['testdoxHTMLFile'] = $loggingConfiguration['testdox-html'];
            }

            if (isset($loggingConfiguration['testdox-text']) && !isset($arguments['testdoxTextFile'])) {
                $arguments['testdoxTextFile'] = $loggingConfiguration['testdox-text'];
            }
        }

        $arguments['backupGlobals']               = isset($arguments['backupGlobals'])               ? $arguments['backupGlobals']               : NULL;
        $arguments['backupStaticAttributes']      = isset($arguments['backupStaticAttributes'])      ? $arguments['backupStaticAttributes']      : NULL;
        $arguments['cpdMinLines']                 = isset($arguments['cpdMinLines'])                 ? $arguments['cpdMinLines']                 : 5;
        $arguments['cpdMinMatches']               = isset($arguments['cpdMinMatches'])               ? $arguments['cpdMinMatches']               : 70;
        $arguments['colors']                      = isset($arguments['colors'])                      ? $arguments['colors']                      : FALSE;
        $arguments['convertErrorsToExceptions']   = isset($arguments['convertErrorsToExceptions'])   ? $arguments['convertErrorsToExceptions']   : TRUE;
        $arguments['convertNoticesToExceptions']  = isset($arguments['convertNoticesToExceptions'])  ? $arguments['convertNoticesToExceptions']  : TRUE;
        $arguments['convertWarningsToExceptions'] = isset($arguments['convertWarningsToExceptions']) ? $arguments['convertWarningsToExceptions'] : TRUE;
        $arguments['excludeGroups']               = isset($arguments['excludeGroups'])               ? $arguments['excludeGroups']               : array();
        $arguments['groups']                      = isset($arguments['groups'])                      ? $arguments['groups']                      : array();
        $arguments['logIncompleteSkipped']        = isset($arguments['logIncompleteSkipped'])        ? $arguments['logIncompleteSkipped']        : FALSE;
        $arguments['processIsolation']            = isset($arguments['processIsolation'])            ? $arguments['processIsolation']            : FALSE;
        $arguments['reportCharset']               = isset($arguments['reportCharset'])               ? $arguments['reportCharset']               : 'ISO-8859-1';
        $arguments['reportHighlight']             = isset($arguments['reportHighlight'])             ? $arguments['reportHighlight']             : FALSE;
        $arguments['reportHighLowerBound']        = isset($arguments['reportHighLowerBound'])        ? $arguments['reportHighLowerBound']        : 70;
        $arguments['reportLowUpperBound']         = isset($arguments['reportLowUpperBound'])         ? $arguments['reportLowUpperBound']         : 35;
        $arguments['reportYUI']                   = isset($arguments['reportYUI'])                   ? $arguments['reportYUI']                   : TRUE;
        $arguments['stopOnFailure']               = isset($arguments['stopOnFailure'])               ? $arguments['stopOnFailure']               : FALSE;

        if ($arguments['filter'] !== FALSE && preg_match('/^[a-zA-Z0-9_]/', $arguments['filter'])) {
            $arguments['filter'] = '/' . $arguments['filter'] . '/';
        }
    }
}
?>
