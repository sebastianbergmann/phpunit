<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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

@include_once 'Image/GraphViz.php';

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Runner/BaseTestRunner.php';
require_once 'PHPUnit/Util/Report/Test/Node.php';
require_once 'PHPUnit/Util/Report/Test/Node/Test.php';
require_once 'PHPUnit/Util/Filesystem.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Represents a PHPUnit_Framework_TestSuite object in the test hierarchy.
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
class PHPUnit_Util_Report_Test_Node_TestSuite extends PHPUnit_Util_Report_Test_Node
{
    /**
     * @var    PHPUnit_Util_Report_Test_Node[]
     * @access protected
     */
    protected $children = array();

    /**
     * @var    PHPUnit_Util_Report_Test_Node_TestSuite[]
     * @access protected
     */
    protected $suites = array();

    /**
     * @var    PHPUnit_Util_Report_Test_Node_Test[]
     * @access protected
     */
    protected $tests = array();

    /**
     * Adds a new test suite.
     *
     * @param  string $name
     * @return PHPUnit_Util_Report_Test_Node_TestSuite
     * @access public
     */
    public function addTestSuite($name)
    {
        $suite = new PHPUnit_Util_Report_Test_Node_TestSuite($name, $this);

        $this->children[] = $suite;
        $this->suites[]   = &$this->children[count($this->children)-1];

        return $suite;
    }

    /**
     * Adds a new test.
     *
     * @param  string                  $name
     * @param  PHPUnit_Framework_Test $object
     * @param  mixed                   $result
     * @access public
     */
    public function addTest($name, PHPUnit_Framework_Test $object, $result)
    {
        $test = new PHPUnit_Util_Report_Test_Node_Test($name, $this, $object, $result);

        $this->children[] = $test;
        $this->tests[]    = &$this->children[count($this->children)-1];
    }

    /**
     * Returns the corresponding PHPUnit_Util_Report_Test_Node_Test object
     * for a given PHPUnit_Framework_Test object.
     *
     * @param  PHPUnit_Framework_Test $test
     * @return PHPUnit_Util_Report_Test_Node_Test
     * @access public
     */
    public function lookupTest(PHPUnit_Framework_Test $test)
    {
        /*
        foreach ($this->tests as $child) {
            if ($test === $child->getObject()) {
                return $child;
            }
        }

        foreach ($this->suites as $child) {
            $result = $child->lookupTest($test);

            if ($result !== FALSE) {
                return $result;
            }
        }

        return FALSE;
        */

        return $test->__testNode;
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
        $this->doRender($target, $title);

        foreach ($this->children as $child) {
            $child->render($target, $title);
        }
    }

    /**
     * @param  string   $target
     * @access protected
     */
    protected function doRender($target, $title)
    {
        $file = $target . PHPUnit_Util_Filesystem::getSafeFilename($this->getName()) . '-test.htm';

        $template = new PHPUnit_Util_Template(
          PHPUnit_Util_Report::getTemplatePath() .
          'testsuite.htm'
        );

        $this->setTemplateVars($template, $title);
        $this->setGraphVizTemplateVars($template, $target);

        $template->setVar(
          array(
            'items',
            'testmap_image',
            'testmap'
          ),
          array(
            $this->renderItems(),
            '',
            ''
          )
        );

        $template->renderTo($file);
    }

    /**
     * @return string
     * @access protected
     */
    protected function renderItems()
    {
        $result = '';

        foreach ($this->tests as $item) {
            $itemTemplate = new PHPUnit_Util_Template(
              PHPUnit_Util_Report::getTemplatePath() .
              'testsuite_item.htm'
            );

            $resultCode = $item->getResult();

            if ($resultCode instanceof PHPUnit_Framework_TestFailure) {
                if ($resultCode->isFailure()) {
                    $testResult = 'Failure';
                }

                else if ($resultCode->thrownException() instanceof PHPUnit_Framework_SkippedTest) {
                    $testResult = 'Skipped';
                }

                else if ($resultCode->thrownException() instanceof PHPUnit_Framework_IncompleteTest) {
                    $testResult = 'Incomplete';
                }

                else {
                    $testResult = 'Error';
                }
            }

            else if ($resultCode === PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                $testResult = 'Passed';
            }

            else {
                $testResult = 'Error';
            }

            switch ($testResult) {
                case 'Passed':
                case 'Skipped': {
                    $color = 'chameleon';
                }
                break;

                case 'Incomplete': {
                    $color = 'butter';
                }
                break;

                case 'Error':
                case 'Failure':
                default: {
                    $color = 'scarlet_red';
                }
            }

            $itemTemplate->setVar(
              array(
                'color',
                'result',
                'name',
              ),
              array(
                $color,
                $testResult,
                $item->getName(),
              )
            );

            $result .= $itemTemplate->render();
        }

        return $result;
    }

    /**
     * @param  PHPUnit_Util_Template $template
     * @param  string                 $target
     * @access public
     */
    protected function setGraphVizTemplateVars(PHPUnit_Util_Template $template, $target)
    {
        $testmap = '';
        $testmap_image = 'snow.png';
        $safeName = PHPUnit_Util_Filesystem::getSafeFilename($this->getName());
        $dotFile = $target . $safeName . '.dot';

        if (file_exists($dotFile)) {
            $pngFile = $target . $safeName . '.png';
            $mapFile = $target . $safeName . '.map';

            $graphviz = new Image_GraphViz(TRUE);

            $success = $graphviz->renderDotFile($dotFile, $pngFile, 'png');

            if ($success) {
                $testmap_image = basename($pngFile);
            }

            $success = $graphviz->renderDotFile($dotFile, $mapFile, 'cmapx');

            if ($success) {
                $testmap = file_get_contents($mapFile);
                unlink($mapFile);
            }
        }

        $template->setVar(
          array(
            'testmap',
            'testmap_image'
          ),
          array(
            $testmap,
            $testmap_image
          )
        );
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
