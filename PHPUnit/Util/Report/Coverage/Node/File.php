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
require_once 'PHPUnit/Util/Array.php';
require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/SourceFile.php';
require_once 'PHPUnit/Util/Template.php';
require_once 'PHPUnit/Util/Report/Coverage/Node.php';
require_once 'PHPUnit/Util/Report/Test/Node/TestSuite.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 *
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
class PHPUnit_Util_Report_Coverage_Node_File extends PHPUnit_Util_Report_Coverage_Node
{
    /**
     * @var    PHPUnit_Util_SourceFile
     * @access protected
     */
    protected $sourceFile;

    /**
     * @var    array
     * @access protected
     */
    protected $coveringTests = array();

    /**
     * @var    array
     * @access protected
     */
    protected $coveringTestsByLine = array();

    /**
     * @var    array
     * @access protected
     */
    protected $executedLines;

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
     * Constructor.
     *
     * @param  string                         $name
     * @param  PHPUnit_Util_CodeCoverage_Node $parent
     * @param  array                          $lines
     * @throws RuntimeException
     * @access public
     */
    public function __construct($name, PHPUnit_Util_Report_Coverage_Node $parent, array $executedLines)
    {
        parent::__construct($name, $parent);

        $path = $this->getPath();

        if (!file_exists($path)) {
            throw new RuntimeException;
        }

        $this->sourceFile    = new PHPUnit_Util_SourceFile($path);
        $this->executedLines = $executedLines;
    }

    /**
     * @param  PHPUnit_Util_Report_Test_Node_TestSuite $testSuite
     * @param  array                                    $files
     * @access protected
     * @static
     */
    public function setupCoveringTests(PHPUnit_Util_Report_Test_Node_TestSuite $testSuite, $files)
    {
        $testCase = array();
        $thisName = $this->getName(TRUE);

        foreach ($files[$thisName] as $line => $tests) {
            if (is_array($tests)) {
                foreach ($tests as $test) {
                    if (isset($test->__testNode->testId)) {
                        $testId = $test->__testNode->testId;

                        if (!isset($testCase[$testId])) {
                            $testCase[$testId] = array(
                              'numLinesExecuted' => 1,
                              'object' => $test
                            );
                        } else {
                            $testCase[$testId]['numLinesExecuted']++;
                        }

                        if (!isset($this->coveringTestsByLine[$line])) {
                            $this->coveringTestsByLine[$line] = array();
                        }

                        $found = FALSE;

                        foreach ($this->coveringTestsByLine[$line] as $_test) {
                            if ($_test === $test) {
                                $found = TRUE;
                                break;
                            }
                        }

                        if (!$found) {
                            $this->coveringTestsByLine[$line][] = $test;
                        }
                    }
                }
            }
        }

        foreach ($testCase as $coveringTest) {
            $test = $coveringTest['object'];
            $test = $test->__testNode;
            $name = $test->getName(TRUE);

            if (!isset($this->coveringTests[$name[0]])) {
                $this->coveringTests[$name[0]] = array();
            }

            $found = FALSE;

            foreach ($this->coveringTests[$name[0]] as $_name => $existingTest) {
                if ($existingTest['object'] === $test) {
                    $found = TRUE;

                    break;
                }
            }

            if (!$found) {
                $test->addCoveredFile($this);

                $this->coveringTests[$name[0]][$name[1]] = array(
                  'numLinesExecuted' => $coveringTest['numLinesExecuted'],
                  'object'           => $test
                );
            } else {
                $this->coveringTests[$name[0]][$name[1]]['numLinesExecuted'] += $coveringTest['numLinesExecuted'];
            }
        }

        $this->coveringTests = PHPUnit_Util_Array::sortRecursively($this->coveringTests);
    }

    /**
     * Returns the tests covering this file.
     *
     * @return array
     * @access public
     */
    public function getCoveringTests()
    {
        return $this->coveringTests;
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
            $this->countLines();
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
            $this->countLines();
        }

        return $this->numExecutedLines;
    }

    /**
     * Counts the executable and executed lines.
     *
     * @access private
     */
    private function countLines()
    {
        $this->numExecutableLines = 0;
        $this->numExecutedLines = 0;

        foreach ($this->executedLines as $line) {
            // Array: Line is executable and was executed.
            if (is_array($line)) {
                $this->numExecutableLines++;
                $this->numExecutedLines++;
            }

            // -1: Line is executable and was not executed.
            else if ($line == -1) {
                $this->numExecutableLines++;
            }
        }
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
        $template = new PHPUnit_Util_Template(
          PHPUnit_Util_Report::getTemplatePath() .
          'coverage_file.html'
        );

        $i     = 1;
        $lines = '';

        foreach ($this->sourceFile->highlight() as $line) {
            $css = '';

            if (isset($this->executedLines[$i])) {
                $count = '';

                // Array: Line is executable and was executed.
                // count(Array) = Number of tests that hit this line.
                if (is_array($this->executedLines[$i])) {
                    $color = 'lineCov';
                    $count = sprintf('%8d', count($this->executedLines[$i]));
                }

                // -1: Line is executable and was not executed.
                else if ($this->executedLines[$i] == -1) {
                    $color = 'lineNoCov';
                    $count = sprintf('%8d', 0);
                }

                // -2: Line is dead code.
                else {
                    $color = 'lineDeadCode';
                    $count = '        ';
                }

                $css = sprintf(
                  '<span class="%s">       %s : ',

                  $color,
                  $count
                );
            }

            $lines .= sprintf(
              '<span class="lineNum"><a name="%d"></a><a href="#%d">%8d</a> </span>%s%s%s' . "\n",

              $i,
              $i,
              $i,
              !empty($css) ? $css : '                : ',
              $line . str_repeat(' ', $this->sourceFile->getFillup($i)),
              !empty($css) ? '</span>' : ''
            );

            $i++;
        }

        $this->setTemplateVars($template, $title);
        $template->setVar('lines', $lines);

        $cleanId = PHPUnit_Util_Filesystem::getSafeFilename($this->getId());
        $template->renderTo($target . $cleanId . '.html');
    }
}
?>
