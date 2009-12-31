<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
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
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
abstract class PHPUnit_Util_Report_Node
{
    /**
     * @var    array
     */
    protected $cache = array();

    /**
     * @var    string
     */
    protected $name;

    /**
     * @var    PHPUnit_Util_Report_Node
     */
    protected $parent;

    /**
     * Constructor.
     *
     * @param  string                   $name
     * @param  PHPUnit_Util_Report_Node $parent
     */
    public function __construct($name, PHPUnit_Util_Report_Node $parent = NULL)
    {
        $this->name   = $name;
        $this->parent = $parent;
    }

    /**
     * Returns the percentage of classes that has been tested.
     *
     * @return integer
     */
    public function getTestedClassesPercent()
    {
        return $this->calculatePercent(
          $this->getNumTestedClasses(),
          $this->getNumClasses()
        );
    }

    /**
     * Returns the percentage of methods that has been tested.
     *
     * @return integer
     */
    public function getTestedMethodsPercent()
    {
        return $this->calculatePercent(
          $this->getNumTestedMethods(),
          $this->getNumMethods()
        );
    }

    /**
     * Returns the percentage of executed lines.
     *
     * @return integer
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
     */
    public function getId()
    {
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
     */
    public function getLink($full)
    {
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
     */
    public function getPath()
    {
        if (!isset($this->cache['path'])) {
            if ($this->parent === NULL) {
                $this->cache['path'] = $this->getName(FALSE, TRUE);
            } else {
                if (substr($this->parent->getPath(), -1) == DIRECTORY_SEPARATOR) {
                    $this->cache['path'] = $this->parent->getPath() .
                                           $this->getName(FALSE, TRUE);
                } else {
                    $this->cache['path'] = $this->parent->getPath() .
                                           DIRECTORY_SEPARATOR .
                                           $this->getName(FALSE, TRUE);

                    if ($this->parent->getPath() === '' &&
                        realpath($this->cache['path']) === FALSE &&
                        realpath($this->getName(FALSE, TRUE)) !== FALSE) {
                        $this->cache['path'] = $this->getName(FALSE, TRUE);
                    }
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
     */
    protected function calculatePercent($a, $b)
    {
        if ($b > 0) {
            $percent = ($a / $b) * 100;
        } else {
            $percent = 100;
        }

        return sprintf(
          '%01.2F',
          $percent
        );
    }

    protected function doRenderItemObject(PHPUnit_Util_Report_Node $item, $lowUpperBound, $highLowerBound, $link = NULL, $itemClass = 'coverItem')
    {
        return $this->doRenderItem(
          array(
            'name'                 => $link != NULL ? $link : $item->getLink(FALSE),
            'itemClass'            => $itemClass,
            'numClasses'           => $item->getNumClasses(),
            'numTestedClasses'     => $item->getNumTestedClasses(),
            'testedClassesPercent' => $item->getTestedClassesPercent(),
            'numMethods'           => $item->getNumMethods(),
            'numTestedMethods'     => $item->getNumTestedMethods(),
            'testedMethodsPercent' => $item->getTestedMethodsPercent(),
            'numExecutableLines'   => $item->getNumExecutableLines(),
            'numExecutedLines'     => $item->getNumExecutedLines(),
            'executedLinesPercent' => $item->getLineExecutedPercent()
          ),
          $lowUpperBound,
          $highLowerBound
        );
    }

    protected function doRenderItem(array $data, $lowUpperBound, $highLowerBound, $template = NULL)
    {
        if ($template === NULL) {
            if ($this instanceof PHPUnit_Util_Report_Node_Directory) {
                $template = 'directory_item.html';
            } else {
                $template = 'file_item.html';
            }
        }

        $itemTemplate = new PHPUnit_Util_Template(
          PHPUnit_Util_Report::$templatePath . $template
        );

        if ($data['numClasses'] > 0) {
            list($classesColor, $classesLevel) = $this->getColorLevel(
              $data['testedClassesPercent'], $lowUpperBound, $highLowerBound
            );

            $classesNumber = $data['numTestedClasses'] . ' / ' . $data['numClasses'];
        } else {
            $classesColor  = 'snow';
            $classesLevel  = 'None';
            $classesNumber = '&nbsp;';
        }

        if ($data['numMethods'] > 0) {
            list($methodsColor, $methodsLevel) = $this->getColorLevel(
              $data['testedMethodsPercent'], $lowUpperBound, $highLowerBound
            );

            $methodsNumber = $data['numTestedMethods'] . ' / ' . $data['numMethods'];
        } else {
            $methodsColor  = 'snow';
            $methodsLevel  = 'None';
            $methodsNumber = '&nbsp;';
        }

        list($linesColor, $linesLevel) = $this->getColorLevel(
          $data['executedLinesPercent'], $lowUpperBound, $highLowerBound
        );

        if ($data['name'] == '<b><a href="#0">*</a></b>') {
            $functions = TRUE;
        } else {
            $functions = FALSE;
        }

        $itemTemplate->setVar(
          array(
            'name'                     => $functions ? 'Functions' : $data['name'],
            'itemClass'                => isset($data['itemClass']) ? $data['itemClass'] : 'coverItem',
            'classes_color'            => $classesColor,
            'classes_level'            => $functions ? 'None' : $classesLevel,
            'classes_tested_width'     => floor($data['testedClassesPercent']),
            'classes_tested_percent'   => !$functions && $data['numClasses'] > 0 ? $data['testedClassesPercent'] . '%' : '&nbsp;',
            'classes_not_tested_width' => 100 - floor($data['testedClassesPercent']),
            'classes_number'           => $functions ? '&nbsp;' : $classesNumber,
            'methods_color'            => $methodsColor,
            'methods_level'            => $methodsLevel,
            'methods_tested_width'     => floor($data['testedMethodsPercent']),
            'methods_tested_percent'   => $data['numMethods'] > 0 ? $data['testedMethodsPercent'] . '%' : '&nbsp;',
            'methods_not_tested_width' => 100 - floor($data['testedMethodsPercent']),
            'methods_number'           => $methodsNumber,
            'lines_color'              => $linesColor,
            'lines_level'              => $linesLevel,
            'lines_executed_width'     => floor($data['executedLinesPercent']),
            'lines_executed_percent'   => $data['executedLinesPercent'] . '%',
            'lines_not_executed_width' => 100 - floor($data['executedLinesPercent']),
            'num_executable_lines'     => $data['numExecutableLines'],
            'num_executed_lines'       => $data['numExecutedLines']
          )
        );

        return $itemTemplate->render();
    }

    protected function getColorLevel($percent, $lowUpperBound, $highLowerBound)
    {
        $floorPercent = floor($percent);

        if ($floorPercent < $lowUpperBound) {
            $color = 'scarlet_red';
            $level = 'Lo';
        }

        else if ($floorPercent >= $lowUpperBound &&
                 $floorPercent <  $highLowerBound) {
            $color = 'butter';
            $level = 'Med';
        }

        else {
            $color = 'chameleon';
            $level = 'Hi';
        }

        return array($color, $level);
    }

    protected function renderTotalItem($lowUpperBound, $highLowerBound, $directory = TRUE)
    {
        if ($directory && empty($this->directories) && count($this->files) == 1) {
            return '';
        }

        return $this->doRenderItemObject($this, $lowUpperBound, $highLowerBound, 'Total') .
               "        <tr>\n" .
               '          <td class="tableHead" colspan="10">&nbsp;</td>' . "\n" .
               "        </tr>\n";
    }

    /**
     * @param  PHPUnit_Util_Template $template
     * @param  string                $title
     * @param  string                $charset
     */
    protected function setTemplateVars(PHPUnit_Util_Template $template, $title, $charset)
    {
        $template->setVar(
          array(
            'title'                  => $title,
            'charset'                => $charset,
            'link'                   => $this->getLink(TRUE),
            'num_executable_lines'   => $this->getNumExecutableLines(),
            'num_executed_lines'     => $this->getNumExecutedLines(),
            'lines_executed_percent' => $this->getLineExecutedPercent(),
            'date'                   => $template->getDate(),
            'phpunit_version'        => PHPUnit_Runner_Version::id(),
            'xdebug_version'         => phpversion('xdebug'),
            'php_version'            => PHP_VERSION
          )
        );
    }

    /**
     * Returns the classes of this node.
     *
     * @return array
     */
    abstract public function getClasses();

    /**
     * Returns the number of executable lines.
     *
     * @return integer
     */
    abstract public function getNumExecutableLines();

    /**
     * Returns the number of executed lines.
     *
     * @return integer
     */
    abstract public function getNumExecutedLines();

    /**
     * Returns the number of classes.
     *
     * @return integer
     */
    abstract public function getNumClasses();

    /**
     * Returns the number of tested classes.
     *
     * @return integer
     */
    abstract public function getNumTestedClasses();

    /**
     * Returns the number of methods.
     *
     * @return integer
     */
    abstract public function getNumMethods();

    /**
     * Returns the number of tested methods.
     *
     * @return integer
     */
    abstract public function getNumTestedMethods();

    /**
     * Renders this node.
     *
     * @param string  $target
     * @param string  $title
     * @param string  $charset
     * @param integer $lowUpperBound
     * @param integer $highLowerBound
     */
    abstract public function render($target, $title, $charset = 'ISO-8859-1', $lowUpperBound = 35, $highLowerBound = 70);
}
?>
