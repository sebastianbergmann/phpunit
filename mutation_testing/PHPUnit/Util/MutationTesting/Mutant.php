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
 * PHPUnit_Util_Mutant contains a mutated source file and a description of the mutation.
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
class PHPUnit_Util_MutationTesting_Mutant extends PHPUnit_Util_Source
{
    /**
     * The mutant operator used to create the mutant.
     *
     * @var    PHPUnit_Util_MutantOperator
     * @access private
     */
    private $mutantOp;

    /**
     * The line on which the mutation took place.
     *
     * @var    int
     * @access private
     */
    private $mutatedLine;

    /**
     * The replaced operator.
     *
     * @var    string
     * @access private
     */
    private $replacedOp;

    /**
     * Constructor.
     *
     * @param  string $fileName
     * @param  PHPUnit_Util_MutantOperator $mutantOperator
     * @param  int $line
     * @param  string $replaced
     * @access public
     */
    public function __construct($fileName, PHPUnit_Util_MutationTesting_MutantOperator $mutantOperator, $line, $replaced)
    {
        $this->setFile($fileName);

        $this->mutantOp    = $mutantOperator;
        $this->mutatedLine = $line;
        $this->replacedOp  = $replaced;
     }

    /**
     * Unlinks the source file of the killed mutant.
     *
     * @access    public
     */
    public function kill()
    {
        if (unlink(parent::source) === FALSE) {
            throw new RuntimeException("PHPUnit_Util_Mutant: Error deleting temporary file.");
        }
    }

    /**
     * Returns the line on which the mutation took place.
     *
     * @return int
     * @access public
     */
    public function getLine()
    {
        return $this->mutatedLine;
    }

    /**
     * Returns the mutant operator.
     *
     * @return PHPUnit_Util_MutantOperator
     * @access public
     */     
    public function getMutantOp()
    {
        return $this->mutantOp;
    }

    /**
     * Returns the replaced operator.
     *
     * @return string
     * @access public
     */
    public function getReplacedOp()
    {
        return $this->replacedOp;
    }
}
?>
