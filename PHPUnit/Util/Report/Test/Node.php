<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Filesystem.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Base class for nodes in the test information tree.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
abstract class PHPUnit_Util_Report_Test_Node
{
    /**
     * @var    string
     * @access protected
     */
    protected $name;

    /**
     * @var    PHPUnit_Util_Test_Node
     * @access protected
     */
    protected $parent;

    /**
     * @var    array
     * @access protected
     */
    protected $cache = array();

    /**
     * Constructor.
     *
     * @param  string                  $name
     * @param  PHPUnit_Util_Test_Node $parent
     * @access public
     */
    public function __construct($name, PHPUnit_Util_Report_Test_Node $parent = NULL)
    {
        $this->name   = $name;
        $this->parent = $parent;
    }

    /**
     * Returns this node's name.
     *
     * @param  boolean $includeParent
     * @return mixed
     * @access public
     */
    public function getName($includeParent = FALSE)
    {
        if ($includeParent && $this->parent !== NULL) {
            if (!isset($this->cache['nameIncludingParent'])) {
                $this->cache['nameIncludingParent'] = array(
                  $this->parent->getName(),
                  $this->name
                );
            }

            return $this->cache['nameIncludingParent'];
        } else {
            return $this->name;
        }
    }

    /**
     * Returns this node's link.
     *
     * @param  boolean $full
     * @return string
     * @access public
     */
    public function getLink($full = FALSE)
    {
        if ($full && $this->parent !== NULL) {
            return sprintf(
              '%s / <a href="%s-test.html">%s</a>',

              $this->parent->getLink(TRUE),
              PHPUnit_Util_Filesystem::getSafeFilename($this->getName()),
              $this->getName()
            );
        } else {
            return sprintf(
              '<a href="%s-test.html">%s</a>',

              PHPUnit_Util_Filesystem::getSafeFilename($this->getName()),
              $this->getName()
            );
        }
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
            'date',
            'phpunit_version',
            'xdebug_version',
          ),
          array(
            $title,
            $this->getLink(TRUE),
            $template->getDate(),
            PHPUnit_Runner_Version::id(),
            phpversion('xdebug')
          )
        );
    }

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
