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
 * @subpackage Util_TestDox
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.3.0
 */

/**
 * Base class for printers of TestDox documentation.
 *
 * @package    PHPUnit
 * @subpackage Util_TestDox
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */
abstract class PHPUnit_Util_TestDox_ResultPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var PHPUnit_Util_TestDox_NamePrettifier
     */
    protected $prettifier;

    /**
     * @var string
     */
    protected $testClass = '';

    /**
     * @var integer
     */
    protected $testStatus = FALSE;

    /**
     * @var string
     */
    protected $testError;

    /**
     * @var array
     */
    protected $tests = array();

    /**
     * @var integer
     */
    protected $successful = 0;

    /**
     * @var integer
     */
    protected $failed = 0;

    /**
     * @var integer
     */
    protected $skipped = 0;

    /**
     * @var integer
     */
    protected $incomplete = 0;

    /**
     * @var string
     */
    protected $testTypeOfInterest = 'PHPUnit_Framework_TestCase';

    /**
     * @var string
     */
    protected $currentTestClassPrettified;

    /**
     * @var string
     */
    protected $currentTestMethodPrettified;

    /**
     * @var boolean Verbose
     */
    protected $verbose;

    /**
     * @var array A lookup map to convert test status into a single character.
     */
    protected $testStatusIndicatorCharMap = array(
            PHPUnit_Runner_BaseTestRunner::STATUS_PASSED     => 'X',
            PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED    => 'S',
            PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE => 'I',
            PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE    => 'F',
            PHPUnit_Runner_BaseTestRunner::STATUS_ERROR      => 'E',
            PHPUnit_Runner_BaseTestRunner::SUITE_METHODNAME  => '-'
        );
    /**
     * Constructor.
     *
     * @param  resource  $out
     */
    public function __construct($out = NULL, $verbose = false)
    {
        parent::__construct($out);

        $this->verbose = $verbose;

        $this->prettifier = new PHPUnit_Util_TestDox_NamePrettifier;
        $this->startRun();
    }

    /**
     * Flush buffer and close output.
     *
     */
    public function flush()
    {
        $this->doEndClass();
        $this->endRun();

        parent::flush();
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
            $this->testError = $e;
            $this->failed++;
        }
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->testError = $e;
            $this->failed++;
        }
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            $this->incomplete++;
        }
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            $this->skipped++;
        }
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->testError = NULL;

        if ($test instanceof $this->testTypeOfInterest) {
            $class = get_class($test);

            if ($this->testClass != $class) {
                if ($this->testClass != '') {
                    $this->doEndClass();
                }

                $this->currentTestClassPrettified = $this->prettifier->prettifyTestClass($class);
                $this->startClass($class);

                $this->testClass = $class;
                $this->tests     = array();
            }

            $prettified = FALSE;

            if ($test instanceof PHPUnit_Framework_TestCase &&
               !$test instanceof PHPUnit_Framework_Warning) {
                $annotations = $test->getAnnotations();

                if (isset($annotations['method']['testdox'][0])) {
                    $this->currentTestMethodPrettified = $annotations['method']['testdox'][0];
                    $prettified                        = TRUE;
                }
            }

            if (!$prettified) {
                $this->currentTestMethodPrettified = $this->prettifier->prettifyTestMethod($test->getName(false));
            }

            if (isset($annotations['method']['dataProviderTestdox'][0])) {
                $tdArgumentSpec = $annotations['method']['dataProviderTestdox'][0];

                // generate sprintf format string
                $formatStr = NULL;
                if (is_numeric($tdArgumentSpec))
                {
                    $formatStr = "%{$tdArgumentSpec}\$s";
                }
                else
                {
                    $formatStr = $tdArgumentSpec;
                }

                // generate pretty test name
                $iterationArgs = $test->getData();
                $iterationTestName = trim(vsprintf($formatStr, $iterationArgs));
                $this->currentTestMethodPrettified .= ": {$iterationTestName}";
            }

            $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;


            // ensure name uniqueness
            if (isset($this->tests[$this->currentTestMethodPrettified]))
            {
                // try to append data set info
                $this->currentTestMethodPrettified .= $test->getDataSetAsString(FALSE);
            }
            if (isset($this->tests[$this->currentTestMethodPrettified])) throw new Exception("Test name already exists: {$this->currentTestMethodPrettified}");

            // initialize data set for this test+iteration
            $this->tests[$this->currentTestMethodPrettified] = array('success' => 0, 'failure' => 0, 'errors' => array());
        }
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($test instanceof $this->testTypeOfInterest) {
            $this->tests[$this->currentTestMethodPrettified]['status'] = $this->testStatusIndicatorCharMap[$this->testStatus];
            if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                $this->tests[$this->currentTestMethodPrettified]['success']++;
            } else {
                $this->tests[$this->currentTestMethodPrettified]['failure']++;
            }

            if ($this->testError)
            {
                $this->tests[$this->currentTestMethodPrettified]['errors'][] = $this->testError;
            }

            $this->currentTestClassPrettified  = NULL;
            $this->currentTestMethodPrettified = NULL;
        }
    }

    /**
     * @since  Method available since Release 2.3.0
     */
    protected function doEndClass()
    {
        foreach ($this->tests as $name => $data) {
            $this->onTest($name, $data['failure'] == 0);
        }

        $this->endClass($this->testClass);
    }

    /**
     * Handler for 'start run' event.
     *
     */
    protected function startRun()
    {
    }

    /**
     * Handler for 'start class' event.
     *
     * @param  string $name
     */
    protected function startClass($name)
    {
    }

    /**
     * Handler for 'on test' event.
     *
     * @param  string  $name
     * @param  boolean $success
     */
    protected function onTest($name, $success = TRUE)
    {
    }

    /**
     * Handler for 'end class' event.
     *
     * @param  string $name
     */
    protected function endClass($name)
    {
    }

    /**
     * Handler for 'end run' event.
     *
     */
    protected function endRun()
    {
    }
}
