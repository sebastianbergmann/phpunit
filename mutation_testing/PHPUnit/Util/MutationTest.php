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


require_once ("Source.php");
require_once ("Mutant.php");
require_once ("ParseTree.php");
require_once ("Operator.php");
require_once ("MutantOperator.php");
require_once ("Scanner.php");

include ("Console/Getopt.php");

/**
 * The main class for mutation testing.
 *
 * @category	Testing
 * @package		PHPUnit
 * @author		Mike Lewis <lewismic@grinnell.edu>
 * @copyright	2007 Mike Lewis <lewismic@grinnell.edu>
 * @version		
 * @link		http://www.phpunit.de/
 * @since		Class available since
 */


		$arguments = handleArguments ();
		if (!isset ($arguments[0]))
			die ("Error: Please enter a filename. (--help for info).\n");
		try {			
			$original = new PHPUnit_Util_Source ($arguments[0]);
			$pt = new PHPUnit_Util_ParseTree ($original->getSource (), "mutantWrite.xsl");
			$operators = getOps ("Mutant.Ops");
			
			$mutants = PHPUnit_Util_Scanner::scan ($pt, $operators);
			foreach ($mutants as $mutant) { 
				echo "Replaced: " . $mutant->getReplacedOp ();
				echo $mutant->getSource () . "\n";
			}
			
			genericRun ($mutants, $original, $arguments[2]);
			
		} catch (Exception $e) {
			echo $e->getMessage () . "\n";
		}
		
		
		
		/**
		 * Reads from $fileName to create mutant operators. Mutant operator information 
		 * is delimited by newline characters. Each line is comma delimited and contains 
		 * the token type, a string representation of the operator, and a set of restrictions.
		 *
		 * @param	string $fileName
		 * @return	array
		 * access	public
		 */
		function getOps ($fileName) 
		{
			//$ops = array ();
			$lines = file ($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			if ($lines == FALSE)
				throw new Exception ("PHPUnit_Util_MutationTest: Error reading $fileName.");
				
			$ops = array ();
			$i = 0;
			foreach ($lines as $line) {
				$params = split (",", $line);
				$ops[$i++] = new PHPUnit_Util_Operator ($params[1], $params[0], $params[2]);
			}
			return ($ops);
		}

		function handleArguments ()
		{
			$arg = array ();
			$cg = new Console_Getopt ();
			$allowedShortOptions = "f:t:sh";
			$allowedLongOptions = array ("file=", "tests=", "stats==", "help");
			$args = $cg->readPHPArgv ();
			$ret = $cg->getopt ($args, $allowedShortOptions, $allowedLongOptions);
		
			if (PEAR::isError ($ret)) {
				die ("Error in command line: " . $ret->getMessage () . "\n");
			}

			$opts = $ret[0];
			if (sizeof($opts) > 0) {
				foreach ($opts as $o) {
					switch ($o[0]) {
						case 'f':
						case '--file':
							$arg[0] = $o[1];
							break;
						case 's':
						case '--stats':
							$arg[1] = TRUE;
							break;
						case '-t':
						case '--tests':
							$arg[2] = $o[2];
							break;
						case 'h':
						case '--help':
							echo "Usage:\n MutationTest.php -f <file>";
							echo " -[s]\n";
							echo "<file> indicates the path to the file ";
							echo "to be tested. -[s] enables the display ";
							echo "of statistics.\n";
							exit (0);
							break;
				}
					
			}
		}
		return $arg;
	}
	
	
	
	function genericRun ($mutants, $source, $testFile)
	{
		
		$testSrc = stripRequire ($source->toString (), $testFile);
		
		foreach ($mutants as $mutant) {
			$fn = "tempTest.php";
			if ( ($fh = fopen ($fn, 'w+')) == FALSE)
				die ("Error: Unable to write to test file!\n");
			fwrite ($fh, $mutant->toString () . "\n" . $testSrc);
			`phpunit $fn`;
		}
	}
	
	function stripRequire ($str, $requireFile)
	{
		$newString = str_replace ("require%". basename ($requireFile) ."%);", "", $str);
		return $str;
	}
		
?>
