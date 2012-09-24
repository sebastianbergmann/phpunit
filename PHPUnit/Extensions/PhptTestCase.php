<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2012, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Extensions_PhptTestCase
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.4
 */

if (stream_resolve_include_path('PEAR/RunTest.php')) {
    $currentErrorReporting = error_reporting(E_ERROR | E_WARNING | E_PARSE);
    require_once 'PEAR/RunTest.php';
    error_reporting($currentErrorReporting);
}

/**
 * Wrapper to run .phpt test cases.
 *
 * @package    PHPUnit
 * @subpackage Extensions_PhptTestCase
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.4
 */
class PHPUnit_Extensions_PhptTestCase implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing
{
    /**
     * The filename of the .phpt file.
     *
     * @var    string
     */
    protected $filename;

    /**
     * Options for PEAR_RunTest.
     *
     * @var    array
     */
    protected $options = array();

    /**
     * Constructs a test case with the given filename.
     *
     * @param  string $filename
     * @param  array  $options
     */
    public function __construct($filename, array $options = array())
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_file($filename)) {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'File "%s" does not exist.',
                $filename
              )
            );
        }

        $this->filename = $filename;
        $this->options  = $options;
    }

    /**
     * Counts the number of test cases executed by run(TestResult result).
     *
     * @return integer
     */
    public function count()
    {
        return 1;
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @param  array                        $options
     * @return PHPUnit_Framework_TestResult
     */
    public function run(PHPUnit_Framework_TestResult $result = NULL, array $options = array())
    {
        if (!class_exists('PEAR_RunTest', FALSE)) {
            throw new PHPUnit_Framework_Exception('Class PEAR_RunTest not found.');
        }

        if (isset($GLOBALS['_PEAR_destructor_object_list']) &&
            is_array($GLOBALS['_PEAR_destructor_object_list']) &&
            !empty($GLOBALS['_PEAR_destructor_object_list'])) {
            $pearDestructorObjectListCount = count($GLOBALS['_PEAR_destructor_object_list']);
        } else {
            $pearDestructorObjectListCount = 0;
        }

        if ($result === NULL) {
            $result = new PHPUnit_Framework_TestResult;
        }

        $coverage = $result->getCollectCodeCoverageInformation();
        $options  = array_merge($options, $this->options);

        if (!isset($options['include_path'])) {
            $options['include_path'] = get_include_path();
        }

        if ($coverage) {
            $options['coverage'] = TRUE;
        } else {
            $options['coverage'] = FALSE;
        }

        $currentErrorReporting = error_reporting(E_ERROR | E_WARNING | E_PARSE);
        $runner                = new PEAR_RunTest(new PHPUnit_Extensions_PhptTestCase_Logger, $options);

        if ($coverage) {
            $runner->xdebug_loaded = TRUE;
        } else {
            $runner->xdebug_loaded = FALSE;
        }

        $result->startTest($this);

        PHP_Timer::start();

        $buffer = $runner->run($this->filename, $options);
        $time   = PHP_Timer::stop();

        error_reporting($currentErrorReporting);

        $base         = basename($this->filename);
        $path         = dirname($this->filename);
        $coverageFile = $path . DIRECTORY_SEPARATOR . str_replace(
                          '.phpt', '.xdebug', $base
                        );
        $diffFile     = $path . DIRECTORY_SEPARATOR . str_replace(
                          '.phpt', '.diff', $base
                        );
        $expFile      = $path . DIRECTORY_SEPARATOR . str_replace(
                          '.phpt', '.exp', $base
                        );
        $logFile      = $path . DIRECTORY_SEPARATOR . str_replace(
                          '.phpt', '.log', $base
                        );
        $outFile      = $path . DIRECTORY_SEPARATOR . str_replace(
                          '.phpt', '.out', $base
                        );
        $phpFile      = $path . DIRECTORY_SEPARATOR . str_replace(
                          '.phpt', '.php', $base
                        );

        if (is_object($buffer) && $buffer instanceof PEAR_Error) {
            $result->addError(
              $this,
              new PHPUnit_Framework_Exception($buffer->getMessage()),
              $time
            );
        }

        else if ($buffer == 'SKIPPED') {
            $result->addFailure($this, new PHPUnit_Framework_SkippedTestError, 0);
        }

        else if ($buffer != 'PASSED') {
            $expContent = file_get_contents($expFile);
            $outContent = file_get_contents($outFile);

            $result->addFailure(
              $this,
              new PHPUnit_Framework_ComparisonFailure(
                $expContent,
                $outContent,
                $expContent,
                $outContent
              ),
              $time
            );
        }

        foreach (array($diffFile, $expFile, $logFile, $phpFile, $outFile) as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        if ($coverage && file_exists($coverageFile)) {
            eval('$coverageData = ' . file_get_contents($coverageFile) . ';');
            unset($coverageData[$phpFile]);

            $result->getCodeCoverage()->append($coverageData, $this);
            unlink($coverageFile);
        }

        $result->endTest($this, $time);

        // Do not invoke PEAR's destructor mechanism for PHP 4
        // as it raises an E_STRICT.
        if ($pearDestructorObjectListCount == 0) {
            unset($GLOBALS['_PEAR_destructor_object_list']);
        } else {
            $count = count($GLOBALS['_PEAR_destructor_object_list']) - $pearDestructorObjectListCount;

            for ($i = 0; $i < $count; $i++) {
                array_pop($GLOBALS['_PEAR_destructor_object_list']);
            }
        }

        return $result;
    }

    /**
     * Returns the name of the test case.
     *
     * @return string
     */
    public function getName()
    {
        return $this->toString();
    }

    /**
     * Returns a string representation of the test case.
     *
     * @return string
     */
    public function toString()
    {
        return $this->filename;
    }
}
