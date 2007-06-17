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
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    
 * @link       http://www.phpunit.de/
 * @since      
 */


/**
 * PHPUnit_Util_Operator describes an operator. 
 *
 * @category	Testing
 * @package		PHPUnit
 * @author		Mike Lewis <lewismic@grinnell.edu>
 * @copyright	2007 Mike Lewis <lewismic@grinnell.edu>
 * @version		
 * @link		http://www.phpunit.de/
 * @since		Class available since
 */
 class PHPUnit_Util_MutationTesting_Operator 
 {
 
     /**
	 * The string representation of the operator.
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $operator;
	
    /**
	 * The token type of the operator.
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $tokenType;
	
    /**
	 * An array describing mutant operators to replace the current operator with.
	 *
	 * @var		array
	 * @access	private
	 */
	private $replaceWith = array ();
 
 	/**
	 * Constructor.
	 *
	 * @param	string $op
	 * @param	string $token
	 * @param	array $restr
	 * @param	string $mutantFile
	 * @access	public
	 */
	 function PHPUnit_Util_MutationTesting_Operator ($op, $token, $mutantFile)
	 {
		$this->operator = $op;
		$this->tokenType = $token;
		$this->replaceWith = $this->_getMutantOps ($mutantFile);
	}

	/**
	 * Returns the string representation of the mutation operator (lexeme).
	 *
	 * @return	string
	 * @access	public
	 */
	 public function getOperator () 
	 {
		return ($this->operator);
	 }
	 
	/**
	 * Returns the token type of the mutation operator.
	 *
	 * @return	string
	 * @access	public
	 */
	 public function getTokenType () 
	 {
		return ($this->tokenType);
	 }
	 
	/**
	 * Returns an array of mutant operators to place the current operator with.
	 *
	 * @return	string
	 * @access	public
	 */
	 public function getReplaceWith () 
	 {
		return ($this->replaceWith);
	 }
	 
 	/**
	 * Returns an array of mutant operators as read from the mutant file.
	 *
	 * @param	string $fileName
	 * @return 	array
	 * @access	private
	 */	
	private function _getMutantOps ($fileName) 
	{
		$lines = file ($fileName, FILE_SKIP_EMPTY_LINES);
		if ($lines == FALSE)
			throw new Exception ("PHPUnit_Util_MutationTest: Error reading $fileName.");
		$mutOps = array ();			
		$i = 0;
		foreach ($lines as $line) {
			$params = split (",", $line);
			$mutOps[$i++] = new PHPUnit_Util_MutationTesting_MutantOperator 
				($params[1], $params[0], $this->_restrictionArray ($params[2]));
		}		
		return ($mutOps);
	}
	
	/**
	 * Splits the string of restriction read from the mutant ops file into an array.
	 * 
	 * @param	string $str
	 * @return	array
	 * @access	private
	 */
	private function _restrictionArray ($str) 
	{
		$results = array ();
		if (strpos ($str, ":")) 
			$results = split (":", $str);
		else
			$results [0] = $str;
			
		return ($results);
	}
}
?>
