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
require_once 'ParseTree.php';

/**
 * PHPUnit_Util_MutationTesting_OriginalSource extends PHPUnit_Util_MutationTesting_Source
 * to provide support for actions on a parse tree.
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
class PHPUnit_Util_MutationTesting_OriginalSource extends PHPUnit_Util_MutationTesting_Source
{
    /**
     * A parse tree of the source code.
     *
     * @var    PHPUnit_Util_MutationTesting_ParseTree
     * @access private
     */
    private $parseTree;
    
    /**
     * Constructor.
     *
     * @param  string $fileName
     * @param  string $stylesheet
     * @access public
     */
    public function __construct($fileName, $stylesheet)
    {
        if (!is_readable ($fileName)) {
            throw new RuntimeException
                ("PHPUnit_Util_MutationTesting_OriginalSource: $fileName not found.");
        }
        
        if (!is_readable ($stylesheet)) {
            throw new RuntimeException
                ("PHPunit_Util_MutationTesting_OriginalSource: $stylesheet not found.");
        }
        
        parent::file     = $fileName;
        parent::code     = file_get_contents ($fileName);
        $this->parseTree = new PHPUnit_Util_MutationTesting_ParseTree
                            (parent::getSourceFile (), $stylesheet);
    }
    
    /**
     * Returns the elements in the source file with tags matching $name.
     *
     * @param  string $name
     * @return NodeList
     * @access public
     */
    public function getElements ($name)
    {
        return $this->parseTree->getElements ($name);
    }
    
    /**
     * Replaces the elements of the given parameters in the parseTree only
     * and saves the code change.
     *
     * @param  array $parameters
     * @access public
     */
    public function replace (array $parameters)
    {
        parent::code = $this->parseTree->replace ($parameters);
    }
}
?>