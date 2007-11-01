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
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/Test.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Base class for nodes in the code coverage information tree.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
abstract class PHPUnit_Util_Report_Node
{
    /**
     * @var    array
     * @access protected
     */
    protected $cache = array();

    /**
     * @var    string
     * @access protected
     */
    protected $name;

    /**
     * @var    PHPUnit_Util_Report_Node
     * @access protected
     */
    protected $parent;

    /**
     * Constructor.
     *
     * @param  string                   $name
     * @param  PHPUnit_Util_Report_Node $parent
     * @access public
     */
    public function __construct($name, PHPUnit_Util_Report_Node $parent = NULL)
    {
        $this->name   = $name;
        $this->parent = $parent;
    }

    /**
     * Returns the percentage of classes of which at least one method
     * has been called at least once..
     *
     * @return integer
     * @access public
     */
    public function getCalledClassesPercent()
    {
        return $this->calculatePercent(
          $this->getNumCalledClasses(),
          $this->getNumClasses()
        );
    }

    /**
     * Returns the percentage of methods that has been called at least once.
     *
     * @return integer
     * @access public
     */
    public function getCalledMethodsPercent()
    {
        return $this->calculatePercent(
          $this->getNumCalledMethods(),
          $this->getNumMethods()
        );
    }

    /**
     * Returns the percentage of executed lines.
     *
     * @return integer
     * @access public
     */
    public function getLineExecutedPercent()
    {
        return $this->calculatePercent(
          $this->getNumExecutedLines(),
          $this->getNumExecutableLines()
        );
    }

    /**
     * Returns this node's ID.
     *
     * @return string
     * @access public
     */
    public function getId() {
        if (!isset($this->cache['id'])) {
            if ($this->parent === NULL) {
                $this->cache['id'] = 'index';
            } else {
                $parentId = $this->parent->getId();

                if ($parentId == 'index') {
                    $this->cache['id'] = $this->getName();
                } else {
                    $this->cache['id'] = $parentId . '_' . $this->getName();
                }
            }
        }

        return $this->cache['id'];
    }

    /**
     * Returns this node's name.
     *
     * @param  boolean $includeParent
     * @return string
     * @access public
     */
    public function getName($includeParent = FALSE, $includeCommonPath = FALSE)
    {
        if ($includeParent && $this->parent !== NULL) {
            if (!isset($this->cache['nameIncludingParent'])) {
                $parent = $this->parent->getName(TRUE);
                $this->cache['nameIncludingParent'] = !empty($parent) ? $parent . '/' . $this->name : $this->name;
            }

            return $this->cache['nameIncludingParent'];
        } else {
            if ($this->parent !== NULL) {
                return $this->name;
            } else {
                return $includeCommonPath ? $this->name : '';
            }
        }
    }

    /**
     * Returns the link to this node.
     *
     * @param  boolean $full
     * @return string
     * @access public
     */
    public function getLink($full) {
        if (substr($this->name, -1) == DIRECTORY_SEPARATOR) {
            $name = substr($this->name, 0, -1);
        } else {
            $name = $this->name;
        }

        $cleanId = PHPUnit_Util_Filesystem::getSafeFilename($this->getId());

        if ($full) {
            if ($this->parent !== NULL) {
                $parent = $this->parent->getLink(TRUE) . DIRECTORY_SEPARATOR;
            } else {
                $parent = '';
            }

            return sprintf(
              '%s<a href="%s.html">%s</a>',
              $parent,
              $cleanId,
              $name
            );
        } else {
            return sprintf(
              '<a href="%s.html">%s</a>',
              $cleanId,
              $name
            );
        }
    }

    /**
     * Returns this node's path.
     *
     * @return string
     * @access public
     */
    public function getPath() {
        if (!isset($this->cache['path'])) {
            if ($this->parent === NULL) {
                $this->cache['path'] = $this->getName(FALSE, TRUE);
            } else {
                if (substr($this->parent->getPath(), -1) == DIRECTORY_SEPARATOR) {
                    $this->cache['path'] = $this->parent->getPath() . $this->getName(FALSE, TRUE);
                } else {
                    $this->cache['path'] = $this->parent->getPath() . DIRECTORY_SEPARATOR . $this->getName(FALSE, TRUE);
                }
            }
        }

        return $this->cache['path'];
    }

    /**
     * Calculates a percentage value.
     *
     * @param  integer $a
     * @param  integer $b
     * @return float   ($a / $b) * 100
     * @access protected
     */
    protected function calculatePercent($a, $b)
    {
        if ($b > 0) {
            $percent = ($a / $b) * 100;
        } else {
            $percent = 100;
        }

        return sprintf(
          '%01.2f',
          $percent
        );
    }

    /**
     * @param  PHPUnit_Util_Template $template
     * @param  string                $title
     * @param  string                $charset
     * @access public
     */
   protected function setTemplateVars(PHPUnit_Util_Template $template, $title, $charset)
    {
        $template->setVar(
          array(
            'title',
            'charset',
            'link',
            'num_executable_lines',
            'num_executed_lines',
            'lines_executed_percent',
            'date',
            'phpunit_version',
            'xdebug_version',
          ),
          array(
            $title,
            $charset,
            $this->getLink(TRUE),
            $this->getNumExecutableLines(),
            $this->getNumExecutedLines(),
            $this->getLineExecutedPercent(),
            $template->getDate(),
            PHPUnit_Runner_Version::id(),
            phpversion('xdebug')
          )
        );
    }

    /**
     * Returns the classes of this node.
     *
     * @return array
     * @access public
     * @abstract
     */
    abstract public function getClasses();

    /**
     * Returns the number of executable lines.
     *
     * @return integer
     * @access public
     * @abstract
     */
    abstract public function getNumExecutableLines();

    /**
     * Returns the number of executed lines.
     *
     * @return integer
     * @access public
     * @abstract
     */
    abstract public function getNumExecutedLines();

    /**
     * Returns the number of classes.
     *
     * @return integer
     * @access public
     * @abstract
     */
    abstract public function getNumClasses();

    /**
     * Returns the number of classes of which at least one method
     * has been called at least once.
     *
     * @return integer
     * @access public
     * @abstract
     */
    abstract public function getNumCalledClasses();

    /**
     * Returns the number of methods.
     *
     * @return integer
     * @access public
     * @abstract
     */
    abstract public function getNumMethods();

    /**
     * Returns the number of methods that has been called at least once.
     *
     * @return integer
     * @access public
     * @abstract
     */
    abstract public function getNumCalledMethods();

    /**
     * Renders this node.
     *
     * @param string  $target
     * @param string  $title
     * @param string  $charset
     * @param boolean $highlight
     * @param integer $lowUpperBound
     * @param integer $highLowerBound
     * @access public
     * @abstract
     */
    abstract public function render($target, $title, $charset = 'ISO-8859-1', $highlight = FALSE, $lowUpperBound = 35, $highLowerBound = 70);
}
?>
