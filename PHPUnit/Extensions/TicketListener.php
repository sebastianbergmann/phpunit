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
 * @subpackage Extensions_TicketListener
 * @author     Sean Coates <sean@caedmon.net>
 * @author     Raphael Stolt <raphael.stolt@gmail.com>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

/**
 * Base class for test listeners that interact with an issue tracker.
 *
 * @package    PHPUnit
 * @subpackage Extensions_TicketListener
 * @author     Sean Coates <sean@caedmon.net>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
abstract class PHPUnit_Extensions_TicketListener implements PHPUnit_Framework_TestListener
{
    /**
     * @var array
     */
    protected $ticketCounts = array();

    /**
     * @var boolean
     */
    protected $ran = FALSE;

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
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
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test suite ended.
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
        if (!$test instanceof PHPUnit_Framework_Warning) {
            if ($this->ran) {
                return;
            }

            $name    = $test->getName(FALSE);
            $tickets = PHPUnit_Util_Test::getTickets(get_class($test), $name);

            foreach ($tickets as $ticket) {
                $this->ticketCounts[$ticket][$name] = 1;
            }

            $this->ran = TRUE;
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
        if (!$test instanceof PHPUnit_Framework_Warning) {
            if ($test->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                $ifStatus   = array('assigned', 'new', 'reopened');
                $newStatus  = 'closed';
                $message    = 'Automatically closed by PHPUnit (test passed).';
                $resolution = 'fixed';
                $cumulative = TRUE;
            }

            else if ($test->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
                $ifStatus   = array('closed');
                $newStatus  = 'reopened';
                $message    = 'Automatically reopened by PHPUnit (test failed).';
                $resolution = '';
                $cumulative = FALSE;
            }

            else {
                return;
            }

            $name    = $test->getName(FALSE);
            $tickets = PHPUnit_Util_Test::getTickets(get_class($test), $name);

            foreach ($tickets as $ticket) {
                // Remove this test from the totals (if it passed).
                if ($test->getStatus() == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                    unset($this->ticketCounts[$ticket][$name]);
                }

                // Only close tickets if ALL referenced cases pass
                // but reopen tickets if a single test fails.
                if ($cumulative) {
                    // Determine number of to-pass tests:
                    if (count($this->ticketCounts[$ticket]) > 0) {
                        // There exist remaining test cases with this reference.
                        $adjustTicket = FALSE;
                    } else {
                        // No remaining tickets, go ahead and adjust.
                        $adjustTicket = TRUE;
                    }
                } else {
                    $adjustTicket = TRUE;
                }

                $ticketInfo = $this->getTicketInfo($ticket);

                if ($adjustTicket && in_array($ticketInfo['status'], $ifStatus)) {
                    $this->updateTicket($ticket, $newStatus, $message, $resolution);
                }
            }
        }
    }

    abstract protected function getTicketInfo($ticketId = NULL);
    abstract protected function updateTicket($ticketId, $newStatus, $message, $resolution);
}
