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
 * PHPUnit_Util_Scanner searches a parse tree for potential mutants and creates them.
 *
 * @category	Testing
 * @package		PHPUnit
 * @author		Mike Lewis <lewismic@grinnell.edu>
 * @copyright	2007 Mike Lewis <lewismic@grinnell.edu>
 * @version		
 * @link		http://www.phpunit.de/
 * @since		Class available since
 */
 class PHPUnit_Util_MutationTesting_Scanner
 {
 
	/**
	 * Scans the given parse tree for potential mutants and creates them.
	 *
	 * @param	PHPUnit_Util_ParseTree $pt
	 * @param	array $operators
	 * @return	array
	 * @access	public
	 * @static
	 */
	 public static function scan (PHPUnit_Util_MutationTesting_ParseTree $pt, array $operators) 
	 {
		$mutants = array ();
		$i = 0;
		foreach ($operators as $operator) {
			/* Search the tree for a mutatable token type. */
			$nodeList = $pt->getElements ($operator->getTokenType ());
			foreach ($nodeList as $node) {
				if ($node->nodeType == XML_ELEMENT_NODE) {
					/* Get the ID of the node to replace. */
					$replaceID = $node->getAttribute ("id");
					foreach ($operator->getReplaceWith () as $mutantOp) {
						/* Make a temporary file for the mutated source file. */
						if ( ($tmpFile = tempnam ("/mutants", "MUTANT")) == FALSE)
							throw new Exception ("PHPUnit_Util_Scanner: Error creating temp file.");
						/* Replace the operator and save it to the temp file. */
						$params = array ('searchID' => $replaceID, 'mutantOperator' => $mutantOp->getOperator ());
						$pt->replaceAndSave ($tmpFile, $params); 
						/* Create a new mutant. */
						$mutants[$i++] = new PHPUnit_Util_MutationTesting_Mutant 
							($tmpFile, $mutantOp, 0, $operator->getOperator ());
					} 
				}
			}
		}
		return ($mutants);
	 }
}
?>
