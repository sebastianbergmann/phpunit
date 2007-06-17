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
 * PHPUnit_Util_MutantOperator describes a mutant operator and how it should be applied.
 *
 * @category    Testing
 * @package     PHPUnit
 * @author      Mike Lewis <lewismic@grinnell.edu>
 * @copyright   2007 Mike Lewis <lewismic@grinnell.edu>
 * @version		
 * @link        http://www.phpunit.de/
 * @since       Class available since
 */
 class PHPUnit_Util_MutationTesting_MutantOperator extends PHPUnit_Util_MutationTesting_Operator
 {
	
    /**
	 * An array describing the limits of application for the operator.
	 *
	 * @var	   array
	 * @access private
	 */
	private $restrictions = array ();
	
	
	/**
	 * Constructor.
	 *
	 * @param  string $op
	 * @param  string $token
	 * @param  array $restr
	 * @access public
	 */
	public function __constructor ($op, $token, array $restr = array())
	{
		$this->operator = $op;
		$this->tokenType = $token;
		$this->restrictions = $restr;
	}
	
	 
	/**
	 * Returns true if the mutant is trivial. Not implemented. 
	 *
	 * @param  string $token
	 * @return boolean
	 * @access public
	 */
	public function isTrivial () 
	{

	}
}
?>
