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
 * @subpackage TextUI
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

/**
 * Prints the result of a TextUI TestRunner run.
 *
 * @package    PHPUnit
 * @subpackage TextUI
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_TextUI_ResultPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    const EVENT_TEST_START      = 0;
    const EVENT_TEST_END        = 1;
    const EVENT_TESTSUITE_START = 2;
    const EVENT_TESTSUITE_END   = 3;

    /**
     * @var integer
     */
    protected $column = 0;

    /**
     * @var integer
     */
    protected $maxColumn;

    /**
     * @var boolean
     */
    protected $lastTestFailed = FALSE;

    /**
     * @var integer
     */
    protected $numAssertions = 0;

    /**
     * @var integer
     */
    protected $numTests = -1;

    /**
     * @var integer
     */
    protected $numTestsRun = 0;

    /**
     * @var integer
     */
    protected $numTestsWidth;

    /**
     * @var boolean
     */
    protected $colors = FALSE;

    /**
     * @var boolean
     */
    protected $debug = FALSE;

    /**
     * @var boolean
     */
    protected $verbose = FALSE;

    /**
     * Constructor.
     *
     * @param  mixed   $out
     * @param  boolean $verbose
     * @param  boolean $colors
     * @param  boolean $debug
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.0.0
     */
    public function __construct($out = NULL, $verbose = FALSE, $colors = FALSE, $debug = FALSE)
    {
        parent::__construct($out);

        if (is_bool($verbose)) {
            $this->verbose = $verbose;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'boolean');
        }

        if (is_bool($colors)) {
            $this->colors = $colors;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'boolean');
        }

        if (is_bool($debug)) {
            $this->debug = $debug;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(4, 'boolean');
        }
    }

    /**
     * @param  PHPUnit_Framework_TestResult $result
     */
    public function printResult(PHPUnit_Framework_TestResult $result)
    {
        $this->printHeader();

        if ($result->errorCount() > 0) {
            $this->printErrors($result);
        }

        if ($result->failureCount() > 0) {
            if ($result->errorCount() > 0) {
                print "\n--\n\n";
            }

            $this->printFailures($result);
        }

        if ($this->verbose) {
            if ($result->deprecatedFeaturesCount() > 0) {
                if ($result->failureCount() > 0) {
                    print "\n--\n\nDeprecated PHPUnit features are being used";
                }

                foreach ($result->deprecatedFeatures() as $deprecatedFeature) {
                    $this->write($deprecatedFeature . "\n\n");
                }
            }

            if ($result->notImplementedCount() > 0) {
                if ($result->failureCount() > 0) {
                    print "\n--\n\n";
                }

                $this->printIncompletes($result);
            }

            if ($result->skippedCount() > 0) {
                if ($result->notImplementedCount() > 0) {
                    print "\n--\n\n";
                }

                $this->printSkipped($result);
            }
        }

        $this->printFooter($result);
    }

    /**
     * @param  array   $defects
     * @param  integer $count
     * @param  string  $type
     */
    protected function printDefects(array $defects, $count, $type)
    {
        static $called = FALSE;

        if ($count == 0) {
            return;
        }

        $this->write(
          sprintf(
            "%sThere %s %d %s%s:\n",

            $called ? "\n" : '',
            ($count == 1) ? 'was' : 'were',
            $count,
            $type,
            ($count == 1) ? '' : 's'
          )
        );

        $i = 1;

        foreach ($defects as $defect) {
            $this->printDefect($defect, $i++);
        }

        $called = TRUE;
    }

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     * @param  integer                       $count
     */
    protected function printDefect(PHPUnit_Framework_TestFailure $defect, $count)
    {
        $this->printDefectHeader($defect, $count);
        $this->printDefectTrace($defect);
    }

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     * @param  integer                       $count
     */
    protected function printDefectHeader(PHPUnit_Framework_TestFailure $defect, $count)
    {
        $failedTest = $defect->failedTest();

        if ($failedTest instanceof PHPUnit_Framework_SelfDescribing) {
            $testName = $failedTest->toString();
        } else {
            $testName = get_class($failedTest);
        }

        $this->write(
          sprintf(
            "\n%d) %s\n",

            $count,
            $testName
          )
        );
    }

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     */
    protected function printDefectTrace(PHPUnit_Framework_TestFailure $defect)
    {
        $this->write(
          $defect->getExceptionAsString() . "\n" .
          PHPUnit_Util_Filter::getFilteredStacktrace(
            $defect->thrownException()
          )
        );
        
        $e = $defect->thrownException()->getPrevious();

        while ($e) {
          $this->write(
            "\nCaused by\n" .
            PHPUnit_Framework_TestFailure::exceptionToString($e). "\n" .
            PHPUnit_Util_Filter::getFilteredStacktrace($e)
          );

          $e = $e->getPrevious();
        }
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printErrors(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->errors(), $result->errorCount(), 'error');
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printFailures(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects(
          $result->failures(),
          $result->failureCount(),
          'failure'
        );
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printIncompletes(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects(
          $result->notImplemented(),
          $result->notImplementedCount(),
          'incomplete test'
        );
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     * @since  Method available since Release 3.0.0
     */
    protected function printSkipped(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects(
          $result->skipped(),
          $result->skippedCount(),
          'skipped test'
        );
    }

    protected function printHeader()
    {
        $this->write("\n\n" . PHP_Timer::resourceUsage() . "\n\n");
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printFooter(PHPUnit_Framework_TestResult $result)
    {
        if (count($result) === 0) {
            if ($this->colors) {
                $this->write("\x1b[30;43m\x1b[2K");
            }

            $this->write(
              "No tests executed!\n"
            );

            if ($this->colors) {
                $this->write("\x1b[0m\x1b[2K");
            }
        }

        else if ($result->wasSuccessful() &&
            $result->allCompletlyImplemented() &&
            $result->noneSkipped()) {
            if ($this->colors) {
                $this->write("\x1b[30;42m\x1b[2K");
            }

            $this->write(
              sprintf(
                "OK (%d test%s, %d assertion%s)\n",

                count($result),
                (count($result) == 1) ? '' : 's',
                $this->numAssertions,
                ($this->numAssertions == 1) ? '' : 's'
              )
            );

            if ($this->colors) {
                $this->write("\x1b[0m\x1b[2K");
            }
        }

        else if ((!$result->allCompletlyImplemented() ||
                  !$result->noneSkipped()) &&
                 $result->wasSuccessful()) {
            if ($this->colors) {
                $this->write(
                  "\x1b[30;43m\x1b[2KOK, but incomplete or skipped tests!\n" .
                  "\x1b[0m\x1b[30;43m\x1b[2K"
                );
            } else {
                $this->write("OK, but incomplete or skipped tests!\n");
            }

            $this->write(
              sprintf(
                "Tests: %d, Assertions: %d%s%s.\n",

                count($result),
                $this->numAssertions,
                $this->getCountString(
                  $result->notImplementedCount(), 'Incomplete'
                ),
                $this->getCountString(
                  $result->skippedCount(), 'Skipped'
                )
              )
            );

            if ($this->colors) {
                $this->write("\x1b[0m\x1b[2K");
            }
        }

        else {
            $this->write("\n");

            if ($this->colors) {
                $this->write(
                  "\x1b[37;41m\x1b[2KFAILURES!\n\x1b[0m\x1b[37;41m\x1b[2K"
                );
            } else {
                $this->write("FAILURES!\n");
            }

            $this->write(
              sprintf(
                "Tests: %d, Assertions: %s%s%s%s%s.\n",

                count($result),
                $this->numAssertions,
                $this->getCountString($result->failureCount(), 'Failures'),
                $this->getCountString($result->errorCount(), 'Errors'),
                $this->getCountString(
                  $result->notImplementedCount(), 'Incomplete'
                ),
                $this->getCountString($result->skippedCount(), 'Skipped')
              )
            );

            if ($this->colors) {
                $this->write("\x1b[0m\x1b[2K");
            }
        }

        if (!$this->verbose &&
            $result->deprecatedFeaturesCount() > 0) {
            $message = sprintf(
              "Warning: Deprecated PHPUnit features are being used %s times!\n" .
              "Use --verbose for more information.\n",
              $result->deprecatedFeaturesCount()
            );

            if ($this->colors) {
                $message = "\x1b[37;41m\x1b[2K" . $message .
                           "\x1b[0m";
            }

            $this->write("\n" . $message);
        }
    }

    /**
     * @param  integer $count
     * @param  string  $name
     * @return string
     * @since  Method available since Release 3.0.0
     */
    protected function getCountString($count, $name)
    {
        $string = '';

        if ($count > 0) {
            $string = sprintf(
              ', %s: %d',

              $name,
              $count
            );
        }

        return $string;
    }

    /**
     */
    public function printWaitPrompt()
    {
        $this->write("\n<RETURN> to continue\n");
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
        if ($this->colors) {
            $this->writeProgress("\x1b[31;1mE\x1b[0m");
        } else {
            $this->writeProgress('E');
        }

        $this->lastTestFailed = TRUE;
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
        if ($this->colors) {
            $this->writeProgress("\x1b[41;37mF\x1b[0m");
        } else {
            $this->writeProgress('F');
        }

        $this->lastTestFailed = TRUE;
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
        if ($this->colors) {
            $this->writeProgress("\x1b[33;1mI\x1b[0m");
        } else {
            $this->writeProgress('I');
        }

        $this->lastTestFailed = TRUE;
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
        if ($this->colors) {
            $this->writeProgress("\x1b[36;1mS\x1b[0m");
        } else {
            $this->writeProgress('S');
        }

        $this->lastTestFailed = TRUE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->numTests == -1) {
            $this->numTests      = count($suite);
            $this->numTestsWidth = strlen((string)$this->numTests);
            $this->maxColumn     = 69 - (2 * $this->numTestsWidth);
        }
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
        if ($this->debug) {
            $this->write(
              sprintf(
                "\nStarting test '%s'.\n", PHPUnit_Util_Test::describe($test)
              )
            );
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
        if (!$this->lastTestFailed) {
            $this->writeProgress('.');
        }

        if ($test instanceof PHPUnit_Framework_TestCase) {
            $this->numAssertions += $test->getNumAssertions();
        }

        else if ($test instanceof PHPUnit_Extensions_PhptTestCase) {
            $this->numAssertions++;
        }

        $this->lastTestFailed = FALSE;

        if ($test instanceof PHPUnit_Framework_TestCase) {
            if (!$test->hasPerformedExpectationsOnOutput()) {
                $this->write($test->getActualOutput());
            }
        }
    }

    /**
     * @param  string $progress
     */
    protected function writeProgress($progress)
    {
        $this->write($progress);
        $this->column++;
        $this->numTestsRun++;

        if ($this->column == $this->maxColumn) {
            $this->write(
              sprintf(
                ' %' . $this->numTestsWidth . 'd / %' .
                       $this->numTestsWidth . 'd (%3s%%)',

                $this->numTestsRun,
                $this->numTests,
                floor(($this->numTestsRun / $this->numTests) * 100)
              )
            );

            $this->writeNewLine();
        }
    }

    protected function writeNewLine()
    {
        $this->column = 0;
        $this->write("\n");
    }
}
