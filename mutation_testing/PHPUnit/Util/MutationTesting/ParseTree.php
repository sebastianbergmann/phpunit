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

if (!extension_loaded('sqlite')) {
   $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
   dl($prefix . 'parse_tree.' . PHP_SHLIB_SUFFIX);
}

define('LIBXML_OPTIONS', LIBXML_DTDLOAD | LIBXML_NOENT | LIBXML_DTDATTR | LIBXML_NOCDATA);

/**
 * PHPUnit_Util_ParseTree describes a PHP source code in BNF form.
 *
 * @category	Testing
 * @package		PHPUnit
 * @author		Mike Lewis <lewismic@grinnell.edu>
 * @copyright	2007 Mike Lewis <lewismic@grinnell.edu>
 * @version		
 * @link		http://www.phpunit.de/
 * @since		Class available since
 */
 class PHPUnit_Util_ParseTree
 {
    /**
	 * Describes a PHP source in BNF. 
	 *
	 * @var		DOMDocument
	 * @access	private
	 */
	private $parseTree;
	
    /**
	 * Used to transform a parse tree to a mutated PHP source file.
	 *
	 * @var		XSLTProcessor
	 * @access	private
	 */
	private $toMutantSource;
	
	
	/**
	 * Constructor.
	 *
	 * @param	string $fileName
	 * @param	string $toSourceStyle
	 * @access	public
	 */
	 function PHPUnit_Util_ParseTree ($fileName, $toSourceStyle) 
	 {
		if (!is_readable ($fileName))
			throw new Exception ("PHPUnit_Util_ParseTree: $fileName not found.");
		else if (!is_readable ($toSourceStyle))
			throw new Exception ("PHPUnit_Util_ParseTree: $toSourceStyle not found.");
		else {
			$this->parseTree = new DOMDocument ();
			$xml = new DOMDocument ();
			$this->toMutantSource = new XSLTProcessor ();	
			
			/* Load the parse tree DOM using the "parse_tree" package. */
			if ($this->parseTree->loadXML (parse_tree_from_file ($fileName), LIBXML_OPTIONS) == FALSE)
				throw new Exception ("PHPUnit_Util_ParseTree: Error loading XML from string.");

			/* Set up the XSLTProcessor. */
			$xml->load ($toSourceStyle, LIBXML_OPTIONS);
			$this->toMutantSource->importStyleSheet ($xml);
		}
	 }
	 
	 /**
	  * Fetches the elements in the parse tree with the given tag name. 
	  *
	  * @param	string $name
	  * @return	DOMNodeList
	  * @access	public
	  */		
	  public function getElements ($name) 
	  {
		return ($this->parseTree->getElementsByTagName ($name));
	  }
	  
	 /**
	  * Replaces the node pointed to by $ID in the parse tree with the given
	  * mutant operator and save to $fileName.
	  *
	  * @param	string $fileName
	  * @param	array $params
	  * @access	public
	  */
	  public function replaceAndSave ($fileName, array $params)
	  {
		if ( ($fh = fopen ($fileName, 'w')) == FALSE)
			throw new Exception ("PHPUnit_Util_ParseTree: Error opening file $fileName.");
			
		$ns = $this->parseTree->firstChild->namespaceURI;
		/* Set the $searchID parameter in the toMutantSource XSL Stylesheet. */
		$this->toMutantSource->setParameter ($ns, $params);

		
		/* Save the transformed results. */
		if (fwrite ($fh, $this->toMutantSource->transformToXML ($this->parseTree)) == FALSE)
			throw new Exception ("PHPUnit_Util_ParseTree: Error saving mutated source.");
	  }


	private function _traverse ($n, $level) {
		for ($i=0;$i<$level;$i++)
			echo " ";
		if ($n->nodeType == XML_ELEMENT_NODE)
			echo $n->nodeName . " > " . $n->nodeType . "\n";
		else if ($n->data != "")
			echo $n->data . " > " . $n->nodeType . "\n";
		if ($n->hasChildNodes ()) {
			$level++;
			foreach ($n->childNodes as $child)
				$this->_traverse ($child, $level);
		}
	}
}
?>
	 
