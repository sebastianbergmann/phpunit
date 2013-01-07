<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage TextUI
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

/**
 * A TestRunner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 *
 * @package    PHPUnit
 * @subpackage TextUI
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_TextUI_TestRunner extends PHPUnit_Runner_BaseTestRunner
{
    const SUCCESS_EXIT   = 0;
    const FAILURE_EXIT   = 1;
    const EXCEPTION_EXIT = 2;

    /**
     * @var PHP_CodeCoverage_Filter
     */
    protected $codeCoverageFilter;

    /**
     * @var PHPUnit_Runner_TestSuiteLoader
     */
    protected $loader = NULL;

    /**
     * @var PHPUnit_TextUI_ResultPrinter
     */
    protected $printer = NULL;

    /**
     * @var boolean
     */
    protected static $versionStringPrinted = FALSE;

    /**
     * @param PHPUnit_Runner_TestSuiteLoader $loader
     * @param PHP_CodeCoverage_Filter        $filter
     * @since Method available since Release 3.4.0
     */
    public function __construct(PHPUnit_Runner_TestSuiteLoader $loader = NULL, PHP_CodeCoverage_Filter $filter = NULL)
    {
        if ($filter === NULL) {
            $filter = new PHP_CodeCoverage_Filter;
        }

        $this->codeCoverageFilter = $filter;
        $this->loader             = $loader;
    }

    /**
     * @param  mixed $test
     * @param  array $arguments
     * @throws PHPUnit_Framework_Exception
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
            throw new PHPUnit_Framework_Exception(
              'No test case or test suite found.'
            );
        }
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
            $GLOBALS['__PHPUNIT_BOOTSTRAP'] = $arguments['bootstrap'];
        }

        if ($arguments['backupGlobals'] === FALSE) {
            $suite->setBackupGlobals(FALSE);
        }

        if ($arguments['backupStaticAttributes'] === TRUE) {
            $suite->setBackupStaticAttributes(TRUE);
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

        if ($arguments['stopOnError']) {
            $result->stopOnError(TRUE);
        }

        if ($arguments['stopOnFailure']) {
            $result->stopOnFailure(TRUE);
        }

        if ($arguments['stopOnIncomplete']) {
            $result->stopOnIncomplete(TRUE);
        }

        if ($arguments['stopOnSkipped']) {
            $result->stopOnSkipped(TRUE);
        }

        if ($this->printer === NULL) {
            if (isset($arguments['printer']) &&
                $arguments['printer'] instanceof PHPUnit_Util_Printer) {
                $this->printer = $arguments['printer'];
            } else {
                $this->printer = new PHPUnit_TextUI_ResultPrinter(
                  NULL,
                  $arguments['verbose'],
                  $arguments['colors'],
                  $arguments['debug']
                );
            }
        }

        if (!$this->printer instanceof PHPUnit_Util_Log_TAP &&
            !self::$versionStringPrinted) {
            $this->printer->write(
              PHPUnit_Runner_Version::getVersionString() . "\n\n"
            );

            if (isset($arguments['configuration'])) {
                $this->printer->write(
                  sprintf(
                    "Configuration read from %s\n\n",
                    $arguments['configuration']->getFilename()
                  )
                );
            }
        }

        foreach ($arguments['listeners'] as $listener) {
            $result->addListener($listener);
        }

        $result->addListener($this->printer);

        if ($this->printer instanceof PHPUnit_TextUI_ResultPrinter) {
            $result->addListener(new PHPUnit_Util_DeprecatedFeature_Logger);
        }

        if (isset($arguments['testdoxHTMLFile'])) {
            $result->addListener(
              new PHPUnit_Util_TestDox_ResultPrinter_HTML(
                $arguments['testdoxHTMLFile']
              )
            );
        }

        if (isset($arguments['testdoxTextFile'])) {
            $result->addListener(
              new PHPUnit_Util_TestDox_ResultPrinter_Text(
                $arguments['testdoxTextFile']
              )
            );
        }

        $codeCoverageReports = 0;

        if (extension_loaded('xdebug')) {
            if (isset($arguments['coverageClover'])) {
                $codeCoverageReports++;
            }

            if (isset($arguments['reportDirectory'])) {
                $codeCoverageReports++;
            }

            if (isset($arguments['coveragePHP'])) {
                $codeCoverageReports++;
            }

            if (isset($arguments['coverageText'])) {
                $codeCoverageReports++;
            }
        }

        if ($codeCoverageReports > 0) {
            $codeCoverage = new PHP_CodeCoverage(
              NULL, $this->codeCoverageFilter
            );

            $codeCoverage->setAddUncoveredFilesFromWhitelist(
              $arguments['addUncoveredFilesFromWhitelist']
            );

            $codeCoverage->setProcessUncoveredFilesFromWhitelist(
              $arguments['processUncoveredFilesFromWhitelist']
            );

            if (isset($arguments['forceCoversAnnotation'])) {
                $codeCoverage->setForceCoversAnnotation(
                  $arguments['forceCoversAnnotation']
                );
            }

            if (isset($arguments['mapTestClassNameToCoveredClassName'])) {
                $codeCoverage->setMapTestClassNameToCoveredClassName(
                  $arguments['mapTestClassNameToCoveredClassName']
                );
            }

            $result->setCodeCoverage($codeCoverage);
        }

        if ($codeCoverageReports > 1) {
            if (isset($arguments['cacheTokens'])) {
                $codeCoverage->setCacheTokens($arguments['cacheTokens']);
            }
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

        if (isset($arguments['junitLogfile'])) {
            $result->addListener(
              new PHPUnit_Util_Log_JUnit(
                $arguments['junitLogfile'], $arguments['logIncompleteSkipped']
              )
            );
        }

        if ($arguments['strict']) {
            $result->strictMode(TRUE);

            $result->setTimeoutForSmallTests(
              $arguments['timeoutForSmallTests']
            );

            $result->setTimeoutForMediumTests(
              $arguments['timeoutForMediumTests']
            );

            $result->setTimeoutForLargeTests(
              $arguments['timeoutForLargeTests']
            );
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

        if (isset($codeCoverage)) {
            if (isset($arguments['coverageClover'])) {
                $this->printer->write(
                  "\nGenerating code coverage report in Clover XML format ..."
                );

                $writer = new PHP_CodeCoverage_Report_Clover;
                $writer->process($codeCoverage, $arguments['coverageClover']);

                $this->printer->write(" done\n");
                unset($writer);
            }

            if (isset($arguments['reportDirectory'])) {
                $this->printer->write(
                  "\nGenerating code coverage report in HTML format ..."
                );

                $writer = new PHP_CodeCoverage_Report_HTML(
                  $arguments['reportCharset'],
                  $arguments['reportHighlight'],
                  $arguments['reportLowUpperBound'],
                  $arguments['reportHighLowerBound'],
                  sprintf(
                    ' and <a href="http://phpunit.de/">PHPUnit %s</a>',
                    PHPUnit_Runner_Version::id()
                  )
                );

                $writer->process($codeCoverage, $arguments['reportDirectory']);

                $this->printer->write(" done\n");
                unset($writer);
            }

            if (isset($arguments['coveragePHP'])) {
                $this->printer->write(
                  "\nGenerating code coverage report in PHP format ..."
                );

                $writer = new PHP_CodeCoverage_Report_PHP;
                $writer->process($codeCoverage, $arguments['coveragePHP']);

                $this->printer->write(" done\n");
                unset($writer);
            }

            if (isset($arguments['coverageText'])) {
                if ($arguments['coverageText'] == 'php://stdout') {
                    $outputStream = $this->printer;
                    $colors       = (bool)$arguments['colors'];
                } else {
                    $outputStream = new PHPUnit_Util_Printer($arguments['coverageText']);
                    $colors       = FALSE;
                }

                $writer = new PHP_CodeCoverage_Report_Text(
                  $outputStream,
                  $arguments['reportLowUpperBound'],
                  $arguments['reportHighLowerBound'],
                  $arguments['coverageTextShowUncoveredFiles']
                );

                $writer->process($codeCoverage, $colors);
            }
        }

        return $result;
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
        self::write($message . PHP_EOL);
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
        }

        $arguments['debug']     = isset($arguments['debug'])     ? $arguments['debug']     : FALSE;
        $arguments['filter']    = isset($arguments['filter'])    ? $arguments['filter']    : FALSE;
        $arguments['listeners'] = isset($arguments['listeners']) ? $arguments['listeners'] : array();

        if (isset($arguments['configuration'])) {
            $arguments['configuration']->handlePHPConfiguration();

            $phpunitConfiguration = $arguments['configuration']->getPHPUnitConfiguration();

            if (isset($phpunitConfiguration['backupGlobals']) &&
                !isset($arguments['backupGlobals'])) {
                $arguments['backupGlobals'] = $phpunitConfiguration['backupGlobals'];
            }

            if (isset($phpunitConfiguration['backupStaticAttributes']) &&
                !isset($arguments['backupStaticAttributes'])) {
                $arguments['backupStaticAttributes'] = $phpunitConfiguration['backupStaticAttributes'];
            }

            if (isset($phpunitConfiguration['bootstrap']) &&
                !isset($arguments['bootstrap'])) {
                $arguments['bootstrap'] = $phpunitConfiguration['bootstrap'];
            }

            if (isset($phpunitConfiguration['cacheTokens']) &&
                !isset($arguments['cacheTokens'])) {
                $arguments['cacheTokens'] = $phpunitConfiguration['cacheTokens'];
            }

            if (isset($phpunitConfiguration['colors']) &&
                !isset($arguments['colors'])) {
                $arguments['colors'] = $phpunitConfiguration['colors'];
            }

            if (isset($phpunitConfiguration['convertErrorsToExceptions']) &&
                !isset($arguments['convertErrorsToExceptions'])) {
                $arguments['convertErrorsToExceptions'] = $phpunitConfiguration['convertErrorsToExceptions'];
            }

            if (isset($phpunitConfiguration['convertNoticesToExceptions']) &&
                !isset($arguments['convertNoticesToExceptions'])) {
                $arguments['convertNoticesToExceptions'] = $phpunitConfiguration['convertNoticesToExceptions'];
            }

            if (isset($phpunitConfiguration['convertWarningsToExceptions']) &&
                !isset($arguments['convertWarningsToExceptions'])) {
                $arguments['convertWarningsToExceptions'] = $phpunitConfiguration['convertWarningsToExceptions'];
            }

            if (isset($phpunitConfiguration['processIsolation']) &&
                !isset($arguments['processIsolation'])) {
                $arguments['processIsolation'] = $phpunitConfiguration['processIsolation'];
            }

            if (isset($phpunitConfiguration['stopOnFailure']) &&
                !isset($arguments['stopOnFailure'])) {
                $arguments['stopOnFailure'] = $phpunitConfiguration['stopOnFailure'];
            }

            if (isset($phpunitConfiguration['timeoutForSmallTests']) &&
                !isset($arguments['timeoutForSmallTests'])) {
                $arguments['timeoutForSmallTests'] = $phpunitConfiguration['timeoutForSmallTests'];
            }

            if (isset($phpunitConfiguration['timeoutForMediumTests']) &&
                !isset($arguments['timeoutForMediumTests'])) {
                $arguments['timeoutForMediumTests'] = $phpunitConfiguration['timeoutForMediumTests'];
            }

            if (isset($phpunitConfiguration['timeoutForLargeTests']) &&
                !isset($arguments['timeoutForLargeTests'])) {
                $arguments['timeoutForLargeTests'] = $phpunitConfiguration['timeoutForLargeTests'];
            }

            if (isset($phpunitConfiguration['strict']) &&
                !isset($arguments['strict'])) {
                $arguments['strict'] = $phpunitConfiguration['strict'];
            }

            if (isset($phpunitConfiguration['verbose']) &&
                !isset($arguments['verbose'])) {
                $arguments['verbose'] = $phpunitConfiguration['verbose'];
            }

            if (isset($phpunitConfiguration['forceCoversAnnotation']) &&
                !isset($arguments['forceCoversAnnotation'])) {
                $arguments['forceCoversAnnotation'] = $phpunitConfiguration['forceCoversAnnotation'];
            }

            if (isset($phpunitConfiguration['mapTestClassNameToCoveredClassName']) &&
                !isset($arguments['mapTestClassNameToCoveredClassName'])) {
                $arguments['mapTestClassNameToCoveredClassName'] = $phpunitConfiguration['mapTestClassNameToCoveredClassName'];
            }

            $groupCliArgs = array();
            if (!empty($arguments['groups'])) {
                $groupCliArgs = $arguments['groups'];
            }

            $groupConfiguration = $arguments['configuration']->getGroupConfiguration();

            if (!empty($groupConfiguration['include']) &&
                !isset($arguments['groups'])) {
                $arguments['groups'] = $groupConfiguration['include'];
            }

            if (!empty($groupConfiguration['exclude']) &&
                !isset($arguments['excludeGroups'])) {
                $arguments['excludeGroups'] = array_diff($groupConfiguration['exclude'], $groupCliArgs);
            }

            foreach ($arguments['configuration']->getListenerConfiguration() as $listener) {
                if (!class_exists($listener['class'], FALSE) &&
                    $listener['file'] !== '') {
                    require_once $listener['file'];
                }

                if (class_exists($listener['class'])) {
                    if (count($listener['arguments']) == 0) {
                        $listener = new $listener['class'];
                    } else {
                        $listenerClass = new ReflectionClass(
                                           $listener['class']
                                         );
                        $listener      = $listenerClass->newInstanceArgs(
                                           $listener['arguments']
                                         );
                    }

                    if ($listener instanceof PHPUnit_Framework_TestListener) {
                        $arguments['listeners'][] = $listener;
                    }
                }
            }

            $loggingConfiguration = $arguments['configuration']->getLoggingConfiguration();

            if (isset($loggingConfiguration['coverage-html']) &&
                !isset($arguments['reportDirectory'])) {
                if (isset($loggingConfiguration['charset']) &&
                    !isset($arguments['reportCharset'])) {
                    $arguments['reportCharset'] = $loggingConfiguration['charset'];
                }

                if (isset($loggingConfiguration['highlight']) &&
                    !isset($arguments['reportHighlight'])) {
                    $arguments['reportHighlight'] = $loggingConfiguration['highlight'];
                }

                if (isset($loggingConfiguration['lowUpperBound']) &&
                    !isset($arguments['reportLowUpperBound'])) {
                    $arguments['reportLowUpperBound'] = $loggingConfiguration['lowUpperBound'];
                }

                if (isset($loggingConfiguration['highLowerBound']) &&
                    !isset($arguments['reportHighLowerBound'])) {
                    $arguments['reportHighLowerBound'] = $loggingConfiguration['highLowerBound'];
                }

                $arguments['reportDirectory'] = $loggingConfiguration['coverage-html'];
            }

            if (isset($loggingConfiguration['coverage-clover']) &&
                !isset($arguments['coverageClover'])) {
                $arguments['coverageClover'] = $loggingConfiguration['coverage-clover'];
            }

            if (isset($loggingConfiguration['coverage-php']) &&
                !isset($arguments['coveragePHP'])) {
                $arguments['coveragePHP'] = $loggingConfiguration['coverage-php'];
            }

            if (isset($loggingConfiguration['coverage-text']) &&
                !isset($arguments['coverageText'])) {
                $arguments['coverageText'] = $loggingConfiguration['coverage-text'];
                if (isset($loggingConfiguration['coverageTextShowUncoveredFiles'])) {
                    $arguments['coverageTextShowUncoveredFiles'] = $loggingConfiguration['coverageTextShowUncoveredFiles'];
                } else {
                    $arguments['coverageTextShowUncoveredFiles'] = FALSE;
                }
            }

            if (isset($loggingConfiguration['json']) &&
                !isset($arguments['jsonLogfile'])) {
                $arguments['jsonLogfile'] = $loggingConfiguration['json'];
            }

            if (isset($loggingConfiguration['plain'])) {
                $arguments['listeners'][] = new PHPUnit_TextUI_ResultPrinter(
                  $loggingConfiguration['plain'], TRUE
                );
            }

            if (isset($loggingConfiguration['tap']) &&
                !isset($arguments['tapLogfile'])) {
                $arguments['tapLogfile'] = $loggingConfiguration['tap'];
            }

            if (isset($loggingConfiguration['junit']) &&
                !isset($arguments['junitLogfile'])) {
                $arguments['junitLogfile'] = $loggingConfiguration['junit'];

                if (isset($loggingConfiguration['logIncompleteSkipped']) &&
                    !isset($arguments['logIncompleteSkipped'])) {
                    $arguments['logIncompleteSkipped'] = $loggingConfiguration['logIncompleteSkipped'];
                }
            }

            if (isset($loggingConfiguration['testdox-html']) &&
                !isset($arguments['testdoxHTMLFile'])) {
                $arguments['testdoxHTMLFile'] = $loggingConfiguration['testdox-html'];
            }

            if (isset($loggingConfiguration['testdox-text']) &&
                !isset($arguments['testdoxTextFile'])) {
                $arguments['testdoxTextFile'] = $loggingConfiguration['testdox-text'];
            }

            if ((isset($arguments['coverageClover']) ||
                isset($arguments['reportDirectory']) ||
                isset($arguments['coveragePHP']) ||
                isset($arguments['coverageText'])) &&
                extension_loaded('xdebug')) {

                $filterConfiguration = $arguments['configuration']->getFilterConfiguration();
                $arguments['addUncoveredFilesFromWhitelist'] = $filterConfiguration['whitelist']['addUncoveredFilesFromWhitelist'];
                $arguments['processUncoveredFilesFromWhitelist'] = $filterConfiguration['whitelist']['processUncoveredFilesFromWhitelist'];

                foreach ($filterConfiguration['blacklist']['include']['directory'] as $dir) {
                    $this->codeCoverageFilter->addDirectoryToBlacklist(
                      $dir['path'], $dir['suffix'], $dir['prefix'], $dir['group']
                    );
                }

                foreach ($filterConfiguration['blacklist']['include']['file'] as $file) {
                    $this->codeCoverageFilter->addFileToBlacklist($file);
                }

                foreach ($filterConfiguration['blacklist']['exclude']['directory'] as $dir) {
                    $this->codeCoverageFilter->removeDirectoryFromBlacklist(
                      $dir['path'], $dir['suffix'], $dir['prefix'], $dir['group']
                    );
                }

                foreach ($filterConfiguration['blacklist']['exclude']['file'] as $file) {
                    $this->codeCoverageFilter->removeFileFromBlacklist($file);
                }

                foreach ($filterConfiguration['whitelist']['include']['directory'] as $dir) {
                    $this->codeCoverageFilter->addDirectoryToWhitelist(
                      $dir['path'], $dir['suffix'], $dir['prefix']
                    );
                }

                foreach ($filterConfiguration['whitelist']['include']['file'] as $file) {
                    $this->codeCoverageFilter->addFileToWhitelist($file);
                }

                foreach ($filterConfiguration['whitelist']['exclude']['directory'] as $dir) {
                    $this->codeCoverageFilter->removeDirectoryFromWhitelist(
                      $dir['path'], $dir['suffix'], $dir['prefix']
                    );
                }

                foreach ($filterConfiguration['whitelist']['exclude']['file'] as $file) {
                    $this->codeCoverageFilter->removeFileFromWhitelist($file);
                }
            }
        }

        $arguments['addUncoveredFilesFromWhitelist']     = isset($arguments['addUncoveredFilesFromWhitelist'])     ? $arguments['addUncoveredFilesFromWhitelist']     : TRUE;
        $arguments['processUncoveredFilesFromWhitelist'] = isset($arguments['processUncoveredFilesFromWhitelist']) ? $arguments['processUncoveredFilesFromWhitelist'] : FALSE;
        $arguments['backupGlobals']                      = isset($arguments['backupGlobals'])                      ? $arguments['backupGlobals']                      : NULL;
        $arguments['backupStaticAttributes']             = isset($arguments['backupStaticAttributes'])             ? $arguments['backupStaticAttributes']             : NULL;
        $arguments['cacheTokens']                        = isset($arguments['cacheTokens'])                        ? $arguments['cacheTokens']                        : FALSE;
        $arguments['colors']                             = isset($arguments['colors'])                             ? $arguments['colors']                             : FALSE;
        $arguments['convertErrorsToExceptions']          = isset($arguments['convertErrorsToExceptions'])          ? $arguments['convertErrorsToExceptions']          : TRUE;
        $arguments['convertNoticesToExceptions']         = isset($arguments['convertNoticesToExceptions'])         ? $arguments['convertNoticesToExceptions']         : TRUE;
        $arguments['convertWarningsToExceptions']        = isset($arguments['convertWarningsToExceptions'])        ? $arguments['convertWarningsToExceptions']        : TRUE;
        $arguments['excludeGroups']                      = isset($arguments['excludeGroups'])                      ? $arguments['excludeGroups']                      : array();
        $arguments['groups']                             = isset($arguments['groups'])                             ? $arguments['groups']                             : array();
        $arguments['logIncompleteSkipped']               = isset($arguments['logIncompleteSkipped'])               ? $arguments['logIncompleteSkipped']               : FALSE;
        $arguments['processIsolation']                   = isset($arguments['processIsolation'])                   ? $arguments['processIsolation']                   : FALSE;
        $arguments['repeat']                             = isset($arguments['repeat'])                             ? $arguments['repeat']                             : FALSE;
        $arguments['reportCharset']                      = isset($arguments['reportCharset'])                      ? $arguments['reportCharset']                      : 'UTF-8';
        $arguments['reportHighlight']                    = isset($arguments['reportHighlight'])                    ? $arguments['reportHighlight']                    : FALSE;
        $arguments['reportHighLowerBound']               = isset($arguments['reportHighLowerBound'])               ? $arguments['reportHighLowerBound']               : 70;
        $arguments['reportLowUpperBound']                = isset($arguments['reportLowUpperBound'])                ? $arguments['reportLowUpperBound']                : 35;
        $arguments['stopOnError']                        = isset($arguments['stopOnError'])                        ? $arguments['stopOnError']                        : FALSE;
        $arguments['stopOnFailure']                      = isset($arguments['stopOnFailure'])                      ? $arguments['stopOnFailure']                      : FALSE;
        $arguments['stopOnIncomplete']                   = isset($arguments['stopOnIncomplete'])                   ? $arguments['stopOnIncomplete']                   : FALSE;
        $arguments['stopOnSkipped']                      = isset($arguments['stopOnSkipped'])                      ? $arguments['stopOnSkipped']                      : FALSE;
        $arguments['timeoutForSmallTests']               = isset($arguments['timeoutForSmallTests'])               ? $arguments['timeoutForSmallTests']               : 1;
        $arguments['timeoutForMediumTests']              = isset($arguments['timeoutForMediumTests'])              ? $arguments['timeoutForMediumTests']              : 10;
        $arguments['timeoutForLargeTests']               = isset($arguments['timeoutForLargeTests'])               ? $arguments['timeoutForLargeTests']               : 60;
        $arguments['strict']                             = isset($arguments['strict'])                             ? $arguments['strict']                             : FALSE;
        $arguments['verbose']                            = isset($arguments['verbose'])                            ? $arguments['verbose']                            : FALSE;

        if ($arguments['filter'] !== FALSE &&
            preg_match('/^[a-zA-Z0-9_]/', $arguments['filter'])) {
            // Escape delimiters in regular expression. Do NOT use preg_quote,
            // to keep magic characters.
            $arguments['filter'] = '/' . str_replace(
              '/', '\\/', $arguments['filter']
            ) . '/';
        }
    }
}
