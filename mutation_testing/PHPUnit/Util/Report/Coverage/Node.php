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
 * @since      Class available since Release 3.0.0
 */
abstract class PHPUnit_Util_Report_Coverage_Node
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
     * @var    PHPUnit_Util_CodeCoverage_Node
     * @access protected
     */
    protected $parent;

    /**
     * Constructor.
     *
     * @param  string                          $name
     * @param  PHPUnit_Util_CodeCoverage_Node $parent
     * @access public
     */
    public function __construct($name, PHPUnit_Util_Report_Coverage_Node $parent = NULL)
    {
        $this->name    = $name;
        $this->parent  = $parent;
    }

    /**
     * Returns the percentage of executed lines.
     *
     * @return integer
     * @access public
     */
    public function getExecutedPercent()
    {
        $numExecutableLines = $this->getNumExecutableLines();

        if ($numExecutableLines > 0) {
            $percent = ($this->getNumExecutedLines() / $numExecutableLines) * 100;
        } else {
            $percent = 100;
        }

        return sprintf(
          '%01.2f',
          $percent
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
     * @param  boolean $details
     * @param  boolean $full
     * @return string
     * @access public
     */
    public function getLink($details, $full) {
        if (substr($this->name, -1) == DIRECTORY_SEPARATOR) {
            $name = substr($this->name, 0, -1);
        } else {
            $name = $this->name;
        }

        $cleanId = PHPUnit_Util_Filesystem::getSafeFilename($this->getId());

        if ($full) {
            if ($this->parent !== NULL) {
                $parent = $this->parent->getLink(FALSE, TRUE) . DIRECTORY_SEPARATOR;
            } else {
                $parent = '';
            }

            return sprintf(
              '%s<a href="%s%s.html">%s</a>',
              $parent,
              $cleanId,
              $details ? '-details' : '',
              $name
            );
        } else {
            return sprintf(
              '<a href="%s%s.html">%s</a>',
              $cleanId,
              $details ? '-details' : '',
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
     * @param  PHPUnit_Util_Template $template
     * @param  string                 $title
     * @access public
     */
   protected function setTemplateVars(PHPUnit_Util_Template $template, $title)
    {
        $template->setVar(
          array(
            'title',
            'link',
            'executable_lines',
            'executed_lines',
            'executed_percent',
            'date',
            'phpunit_version',
            'xdebug_version',
          ),
          array(
            $title,
            $this->getLink(FALSE, TRUE),
            $this->getNumExecutableLines(),
            $this->getNumExecutedLines(),
            $this->getExecutedPercent(),
            $template->getDate(),
            PHPUnit_Runner_Version::id(),
            phpversion('xdebug')
          )
        );
    }

    /**
     * Returns the covering tests.
     *
     * @return array
     * @access public
     * @abstract
     */
    abstract public function getCoveringTests();

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
     * Renders this node.
     *
     * @param string $target
     * @param string $title
     * @access public
     * @abstract
     */
    abstract public function render($target, $title);
}
?>
