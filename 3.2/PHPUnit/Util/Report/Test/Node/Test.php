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
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Report/Coverage/Node/File.php';
require_once 'PHPUnit/Util/Report/Test/Node.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Represents a PHPUnit_Framework_Test object in the test hierarchy.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Report_Test_Node_Test extends PHPUnit_Util_Report_Test_Node
{
    /**
     * @var    array
     * @access protected
     */
    protected $coveredFiles;

    /**
     * @var    PHPUnit_Framework_Test
     * @access protected
     */
    protected $object;

    /**
     * @var    mixed
     * @access protected
     */
    protected $result;

    /**
     * Constructor.
     *
     * @param  string                  $name
     * @param  PHPUnit_Util_Test_Node $parent
     * @param  PHPUnit_Framework_Test $object
     * @param  mixed                   $result
     * @access public
     */
    public function __construct($name, PHPUnit_Util_Report_Test_Node $parent, PHPUnit_Framework_Test $object, $result)
    {
        static $testId = 0;

        parent::__construct($name, $parent);

        $this->object = $object;
        $this->result = $result;
        $this->testId = $testId++;

        $this->object->__testNode = $this;
    }

    /**
     * Adds a file that is covered by the test that is represented by this node.
     *
     * @param  PHPUnit_Util_Report_Coverage_Node_File $file
     * @access public
     */
    public function addCoveredFile(PHPUnit_Util_Report_Coverage_Node_File $file)
    {
        $this->coveredFiles[] = $file;
    }

    /**
     * Returns the PHPUnit_Framework_Test object that is represented by this node.
     *
     * @return PHPUnit_Framework_Test
     * @access public
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Returns the result of the PHPUnit_Framework_Test object that is
     * represented by this node.
     *
     * @return mixed
     * @access public
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Renders this node.
     *
     * @param string $target
     * @param string $title
     * @access public
     */
    public function render($target, $title)
    {
    }
}
?>
