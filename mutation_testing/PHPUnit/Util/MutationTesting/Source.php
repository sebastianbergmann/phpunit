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

/**
 * PHPUnit_Util_MutationTesting_Source contains a source file and methods to track
 * tests run on that source.
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
class PHPUnit_Util_MutationTesting_Source 
{
    
    
    /**
     * The path to the source file.
     *
     * @var    string
     * @access protected
     */
    protected $file;
     
    /**
     * A string representation of the source code.
     *
     * @var    string
     * @access protected
     */
    protected $code;

    /**
     * Runs the given test cases on $this and saves the results
     * in an array. Not implemented.
     *
     * @param  array $results
     * @return array
     * @access protected
     */
    protected function runTestCases(PHPUnit_Framework_TestSuite $testSuite, 
                                 PHPUnit_TextUI_TestRunner $runner)
    {
    }

    /**
     * Returns the path to the source file.
     * @return string
     * @access protected
     */
    protected function getSourceFile()
    {
        return $this->file;
    }
    
    /*
     * Returns the string representation of the source code.
     * @return string
     * @access protected
     */
    protected function getSourceCode () 
    {
        return $this->code;    
    }
} 
?>
