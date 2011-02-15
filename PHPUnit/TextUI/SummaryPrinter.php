<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Mattis Stordalen Flister <mattis.stordalen.flister@gmail.com>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release xx
 */

require_once 'PHP/Timer.php';

/**
 * Prints the summary of a testrun
 *
 * @package    PHPUnit
 * @subpackage TextUI
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release xx
 */
class PHPUnit_TextUI_SummaryPrinter {
    /**
     * @var boolean
     */
    protected $verbose = FALSE;

    /**
     * @var boolean
     */
    protected $colors = FALSE;
    
    /**
     * @var PHPUnit_Util_Printer
     */
    protected $printer;
    
    /**
     * @var integer
     */
    protected $numAssertions = 0;

    public function __construct(PHPUnit_Util_Printer $printer, $verbose = NULL, $colors = NULL) {
        $this->printer = $printer;
        
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
    }
    
    public function addAssertions($assertions) {
        $this->numAssertions += $assertions;
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
                    $this->printer->write($deprecatedFeature . "\n\n");
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

        $this->printer->write(
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

        $this->printer->write(
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
        $this->printer->write(
          $defect->getExceptionAsString() . "\n" .
          PHPUnit_Util_Filter::getFilteredStacktrace(
            $defect->thrownException(),
            FALSE
          )
        );
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
        $this->printer->write("\n\n" . PHP_Timer::resourceUsage() . "\n\n");
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printFooter(PHPUnit_Framework_TestResult $result)
    {
        if ($result->wasSuccessful() &&
            $result->allCompletlyImplemented() &&
            $result->noneSkipped()) {
            if ($this->colors) {
                $this->printer->write("\x1b[30;42m\x1b[2K");
            }

            $this->printer->write(
              sprintf(
                "OK (%d test%s, %d assertion%s)\n",

                count($result),
                (count($result) == 1) ? '' : 's',
                $this->numAssertions,
                ($this->numAssertions == 1) ? '' : 's'
              )
            );

            if ($this->colors) {
                $this->printer->write("\x1b[0m\x1b[2K");
            }
        }

        else if ((!$result->allCompletlyImplemented() ||
                  !$result->noneSkipped()) &&
                 $result->wasSuccessful()) {
            if ($this->colors) {
                $this->printer->write(
                  "\x1b[30;43m\x1b[2KOK, but incomplete or skipped tests!\n" .
                  "\x1b[0m\x1b[30;43m\x1b[2K"
                );
            } else {
                $this->printer->write("OK, but incomplete or skipped tests!\n");
            }

            $this->printer->write(
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
                $this->printer->write("\x1b[0m\x1b[2K");
            }
        }

        else {
            $this->printer->write("\n");

            if ($this->colors) {
                $this->printer->write(
                  "\x1b[37;41m\x1b[2KFAILURES!\n\x1b[0m\x1b[37;41m\x1b[2K"
                );
            } else {
                $this->printer->write("FAILURES!\n");
            }

            $this->printer->write(
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
                $this->printer->write("\x1b[0m\x1b[2K");
            }
        }

        if (!$this->verbose &&
            $result->deprecatedFeaturesCount() > 0) {
            $message = sprintf(
              "Warning: Deprecated PHPUnit features are being used %s times!\n".
              "Use --verbose for more information.\n",
              $result->deprecatedFeaturesCount()
            );

            if ($this->colors) {
                $message = "\x1b[37;41m\x1b[2K" . $message .
                           "\x1b[0m";
            }

            $this->printer->write("\n" . $message);
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
}