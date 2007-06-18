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
 * @author     Mike Lewis <lewismic@grinnell.edu>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      File available since Release 4.0.0
 */
 
require_once 'PHPUnit/Framework/TestListener.php';

/**
 * PHPUnit_Util_MutationTesting_MutantListener is responsible for recording and comparing unit
 * test results.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lewis <lewismic@grinnell.edu>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 4.0.0
 */
 
 class PHPUnit_Util_MutationTesting_MutationListener implements PHPUnit_Framework_TestListener
 {
    
    /**
     * The array of test results for the original source.
     *
     * @var    array
     * @access private
     */
    private $original = array ();
    
   /**
    * The current number of errors in the original file.
    *
    * @var    int
    * @access private
    */
    private $numErrors = 0;
    
    /**
     * The current number of failures in the original file.
     *
     * @var    int
     * @access private
     */
    private $numFailures = 0;  
    
    /**
     * The current number of incompletes in the original file.
     *
     * @var    int
     * @access private
     */
    private $numIncomplete = 0;
    
    /**
     * The current number of skipped tests in the original file.
     *
     * @var    int
     * @access private
     */
    private $numSkipped = 0;
    
    /**
     * Indicates whether the listener should be recording the results.
     *
     * @var    boolean
     * @access private
     */
    private $record = TRUE; 

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($this->record) {
            $this->original['error'][++$this->numErrors] = $e->getMessage ();   
        } else {
            if (!array_search ($e->getMessage (), $this->original['error'])) {
                $this->kill ();   
            }   
        }
    }
    
    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     * @access public
     */
    public function addFailure(PHPUnit_Framework_Test $test, 
                               PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($this->record) {
            $this->original['failure'][++$this->numFailures] = $e->getMessage ();   
        } else {
            if (!array_search ($e->getMessage (), $this->original['failure'])) {
                $this->kill ();   
            }   
        }
    }
    
    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($this->record) {
            $this->original['incomplete'][++$this->numIncompete] = $e->getMessage ();   
        } else {
            if (!array_search ($e->getMessage (), $this->original['incomplete'])) {
                $this->kill ();   
            }   
        }
    }
    
    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
       if ($this->record) {
            $this->original['skipped'][++$this->numSkipped] = $e->getMessage ();   
        } else {
            if (!array_search ($e->getMessage (), $this->original['skipped'])) {
                $this->kill ();   
            }   
        } 
    }
    
    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        
    }
    
    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->record) {
            $this->record = FALSE;   
        }
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        
    }
    
    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     * @access public
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        
    }  
 }
 ?>