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

require_once 'Source.php"';
require_once 'Mutant.php';
require_once 'ParseTree.php';
require_once 'Operator.php';
require_once 'MutantOperator.php';
require_once 'Scanner.php';

/**
 * The main class for mutation testing.
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
class PHPUnit_Util_MutationTesting_MutationTest
{
    
    /**
     * mutate drives the creation and testing of mutants.
     *  
     * @param  PHPUnit_TextUI_TestRunner $runner
     * @param  array                     $arguments
     * @access public
     * @static
     */
    public static function mutate(PHPUnit_TextUI_TestRunner $runner, $arguments)
    {
        /*  - We want to create a temp file to hold the contents of the source file
         *    and the test file.
         *  - We will run the tests on the original source code first to gather
         *    information.
         *  - We will mutate the temp file to contain the mutated source and the 
         *    original test file.
         *  - This mutated temp file will be tested. Testing will halt at the point
         *    where the result of a test differs form the result of the original test.
         *    At this point we consider the mutant killed.
         *  - The process will be repeated for each mutant. 
         */
        try {
            $original  = new PHPUnit_Util_MutationTesting_Source($arguments['testFile']);
            $pt        = new PHPUnit_Util_ParseTree($original->getSource(), 'XSL/mutantWrite.xsl');
            $operators = $this->getOps('Operators/Mutant.Ops');
            $mutants   = PHPUnit_Util_MutationTesting_Scanner::scan($pt, $operators);

            foreach ($mutants as $mutant) {
                $testSuite = mutateSuite ($mutant);
                $runner->doRun($testSuite, $arguments);
            }
        }

        catch (Exception $e) {
            print $e->getMessage() . "\n";
        }
    }

    /**
     * mutateSuite modifies the test suite so that it points to the mutated file.
     * 
     * @param  PHPUnit_Util_MutationTesting_Mutant $mutant
     * @return PHPUnit_Framework_TestSuite 
     * @access private 
     */
    private function mutateSuite(PHPUnit_Util_MutationTesting_Mutant $mutant)
    {
        return $this->runner->getTest($mutant->getName(), $mutant->getSource());
    }

    /**
     * Reads from $fileName to create mutant operators. Mutant operator information 
     * is delimited by newline characters. Each line is comma delimited and contains 
     * the token type, a string representation of the operator, and a set of restrictions.
     *
     * @param  string $fileName
     * @return array
     * @access public
     */
    private function getOps($fileName) 
    {
        $lines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === FALSE) {
            throw new RuntimeException
                ("PHPUnit_Util_MutationTesting_MutationTest: Error reading $fileName.");
        }

        $ops = array();
        $i   = 0;

        foreach ($lines as $line) {
            $params    = split (',', $line);
            $ops[$i++] = new PHPUnit_Util_MutationTesting_Operator(
              $params[1], $params[0], $params[2]
            );
        }

        return $ops;
    }    
}
?>
