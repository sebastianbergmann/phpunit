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

require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/Template.php';
require_once 'PHPUnit/Util/Report/Coverage/Node.php';
require_once 'PHPUnit/Util/Report/Coverage/Node/File.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Represents a directory in the code coverage information tree.
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
class PHPUnit_Util_Report_Coverage_Node_Directory extends PHPUnit_Util_Report_Coverage_Node
{
    const LOW_UPPER_BOUND  = 35;
    const HIGH_LOWER_BOUND = 70;

    /**
     * @var    PHPUnit_Util_Report_Coverage_Node[]
     * @access protected
     */
    protected $children = array();

    /**
     * @var    PHPUnit_Util_Report_Coverage_Node_Directory[]
     * @access protected
     */
    protected $directories = array();

    /**
     * @var    PHPUnit_Util_Report_Coverage_Node_File[]
     * @access protected
     */
    protected $files = array();

    /**
     * @var    integer
     * @access protected
     */
    protected $numExecutableLines = -1;

    /**
     * @var    integer
     * @access protected
     */
    protected $numExecutedLines = -1;

    /**
     * Adds a new directory.
     *
     * @return PHPUnit_Util_Report_Coverage_Node_Directory
     * @access public
     */
    public function addDirectory($name)
    {
        $directory = new PHPUnit_Util_Report_Coverage_Node_Directory(
          $name,
          $this
        );

        $this->children[]    = $directory;
        $this->directories[] = &$this->children[count($this->children) - 1];

        return $directory;
    }

    /**
     * Adds a new file.
     *
     * @param  string $name
     * @param  array  $lines
     * @return PHPUnit_Util_Report_Coverage_Node_File
     * @throws RuntimeException
     * @access public
     */
    public function addFile($name, array $lines)
    {
        $file = new PHPUnit_Util_Report_Coverage_Node_File(
          $name,
          $this,
          $lines
        );

        $this->children[] = $file;
        $this->files[]    = &$this->children[count($this->children) - 1];

        $this->numExecutableLines = -1;
        $this->numExecutedLines   = -1;

        return $file;
    }

    /**
     * Returns the directories in this directory.
     *
     * @return
     * @access public
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * Returns the files in this directory.
     *
     * @return
     * @access public
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Returns the tests covering this directory.
     *
     * @return array
     * @access public
     */
    public function getCoveringTests()
    {
        $coveringTests = array();

        foreach ($this->children as $child) {
            $coveringTests = array_merge($coveringTests, $child->getCoveringTests());
        }

        return $coveringTests;
    }

    /**
     * Returns the number of executable lines.
     *
     * @return integer
     * @access public
     */
    public function getNumExecutableLines()
    {
        if ($this->numExecutableLines == -1) {
            $this->numExecutableLines = 0;

            foreach ($this->children as $child) {
                $this->numExecutableLines += $child->getNumExecutableLines();
            }
        }

        return $this->numExecutableLines;
    }

    /**
     * Returns the number of executed lines.
     *
     * @return integer
     * @access public
     */
    public function getNumExecutedLines()
    {
        if ($this->numExecutedLines == -1) {
            $this->numExecutedLines = 0;

            foreach ($this->children as $child) {
                $this->numExecutedLines += $child->getNumExecutedLines();
            }
        }

        return $this->numExecutedLines;
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
        $this->doRender($target, $title, TRUE);
        $this->doRender($target, $title, FALSE);

        foreach ($this->children as $child) {
            $child->render($target, $title);
        }
    }

    /**
     * @param  string   $target
     * @param  boolean  $includeDetails
     * @access protected
     */
    protected function doRender($target, $title, $includeDetails)
    {
        $cleanId = PHPUnit_Util_Filesystem::getSafeFilename($this->getId());
        $file = $target . $cleanId;

        if ($includeDetails) {
            $file .= '-details.html';

            $detailsLink = sprintf(
              '(<a class="detail" href="%s.html">hide details</a>)',
              $cleanId
            );
        } else {
            $file .= '.html';

            $detailsLink = sprintf(
              '(<a class="detail" href="%s-details.html">show details</a>)',
              $cleanId
            );
        }

        $template = new PHPUnit_Util_Template(
          PHPUnit_Util_Report::getTemplatePath() .
          'coverage_directory.html'
        );

        $this->setTemplateVars($template, $title);

        $template->setVar(
          array(
            'items',
            'details_link',
            'low_upper_bound',
            'high_lower_bound'
          ),
          array(
            $this->renderItems($includeDetails),
            $detailsLink,
            self::LOW_UPPER_BOUND,
            self::HIGH_LOWER_BOUND
          )
        );

        $template->renderTo($file);
    }

    /**
     * @param  boolean  $includeDetails
     * @return string
     * @access protected
     */
    protected function renderItems($includeDetails)
    {
        $items  = $this->doRenderItems($this->directories, $includeDetails);
        $items .= $this->doRenderItems($this->files, $includeDetails);

        return $items;
    }

    /**
     * @param  array    $items
     * @param  boolean  $includeDetails
     * @return string
     * @access protected
     */
    protected function doRenderItems(array $items, $includeDetails)
    {
        $result = '';

        foreach ($items as $item) {
            $itemTemplate = new PHPUnit_Util_Template(
              PHPUnit_Util_Report::getTemplatePath() .
              'coverage_item.html'
            );

            $details = '';

            if ($includeDetails) {
                foreach ($item->getCoveringTests() as $suite => $tests) {
                    $detailsHeaderTemplate = new PHPUnit_Util_Template(
                      PHPUnit_Util_Report::getTemplatePath() .
                      'coverage_item_details_header.html'
                    );

                    $detailsHeaderTemplate->setVar(
                      'link',
                      sprintf(
                        '<a href="%s-test.html">%s</a>',

                        PHPUnit_Util_Filesystem::getSafeFilename($suite),
                        $suite
                      )
                    );

                    $details .= $detailsHeaderTemplate->render();

                    foreach ($tests as $test => $_test) {
                        $detailsTemplate = new PHPUnit_Util_Template(
                          PHPUnit_Util_Report::getTemplatePath() .
                          'coverage_item_details.html'
                        );

                        if ($_test['object']->getResult() !== PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                            $failure = sprintf(
                              '<br /><pre>%s</pre>',

                              htmlspecialchars($_test['object']->getResult()->exceptionMessage())
                            );
                        } else {
                            $failure = '';
                        }

                        $detailsTemplate->setVar(
                          array(
                            'item',
                            'executed_percent',
                            'executed_lines',
                            'executable_lines'
                          ),
                          array(
                            $test . $failure,
                            sprintf(
                              '%01.2f',
                              ($_test['numLinesExecuted'] / $item->getNumExecutableLines()) * 100
                            ),
                            $_test['numLinesExecuted'],
                            $item->getNumExecutableLines()
                          )
                        );

                        $details .= $detailsTemplate->render();
                    }
                }
            }

            $floorPercent = floor($item->getExecutedPercent());

            if ($floorPercent < self::LOW_UPPER_BOUND) {
                $color = 'scarlet_red';
                $level = 'Lo';
            }

            else if ($floorPercent >= self::LOW_UPPER_BOUND &&
                     $floorPercent <  self::HIGH_LOWER_BOUND) {
                $color = 'butter';
                $level = 'Med';
            }

            else {
                $color = 'chameleon';
                $level = 'Hi';
            }

            $itemTemplate->setVar(
              array(
                'link',
                'color',
                'level',
                'executed_width',
                'executed_percent',
                'not_executed_width',
                'executable_lines',
                'executed_lines',
                'details'
              ),
              array(
                $item->getLink(FALSE, FALSE),
                $color,
                $level,
                floor($item->getExecutedPercent()),
                $item->getExecutedPercent(),
                100 - floor($item->getExecutedPercent()),
                $item->getNumExecutableLines(),
                $item->getNumExecutedLines(),
                $details
              )
            );

            $result .= $itemTemplate->render();
        }

        return $result;
    }
}
?>
