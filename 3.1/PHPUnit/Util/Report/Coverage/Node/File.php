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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
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
     * @var    array
     * @access protected
     */
    protected $codeLines;

    /**
     * @var    array
     * @access protected
     */
    protected $codeLinesFillup = array();

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

        $this->codeLines     = $this->highlightFile($path);
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

        foreach ($this->codeLines as $line) {
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
              $line . str_repeat(' ', array_shift($this->codeLinesFillup)),
              !empty($css) ? '</span>' : ''
            );

            $i++;
        }

        $this->setTemplateVars($template, $title);
        $template->setVar('lines', $lines);

        $cleanId = PHPUnit_Util_Filesystem::getSafeFilename($this->getId());
        $template->renderTo($target . $cleanId . '.html');
    }

    /**
     * @author Aidan Lister <aidan@php.net>
     * @author Sebastian Bergmann <sb@sebastian-bergmann.de>
     * @param  string $file
     * @return array
     * @access private
     */
    private function highlightFile($file)
    {
        $lines    = file($file);
        $numLines = count($lines);
        $width    = 0;

        for ($i = 0; $i < $numLines; $i++) {
            $lines[$i] = rtrim($lines[$i]);

            if (strlen($lines[$i]) > $width) {
                $width = strlen($lines[$i]);
            }
        }

        for ($i = 0; $i < $numLines; $i++) {
            $this->codeLinesFillup[$i] = $width - strlen($lines[$i]);
        }

        $tokens     = token_get_all(file_get_contents($file));
        $i          = 0;
        $result     = array();
        $result[$i] = '';

        foreach ($tokens as $j => $token) {
            if (is_string($token)) {
                $result[$i] .= sprintf(
                  '<span class="keyword">%s</span>',

                  htmlspecialchars($token)
                );

                list($tb) = isset($tokens[$j - 1]) ? $tokens[$j - 1] : FALSE;

                if ($tb == T_END_HEREDOC) {
                    $result[++$i] = '';
                }

                continue;
            }

            list ($token, $value) = $token;

            $value = str_replace(
              array("\t", ' '),
              array('&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;'),
              htmlspecialchars($value)
            );

            if ($value === "\n") {
                $result[++$i] = '';
            } else {
                $lines = explode("\n", $value);              

                foreach ($lines as $jj => $line) {
                    $line = trim($line);

                    if ($line !== '') {
                        $result[$i] .= sprintf(
                          '<span class="%s">%s</span>',

                          $this->tokenToColor($token),
                          $line
                        );
                    }

                    if (isset($lines[$jj + 1])) {
                        $result[++$i] = '';
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @author Aidan Lister <aidan@php.net>
     * @author Sebastian Bergmann <sb@sebastian-bergmann.de>
     * @param  string $token
     * @return string
     * @access private
     */
    private function tokenToColor($token)
    {
        switch ($token) {
            case T_CONSTANT_ENCAPSED_STRING: return 'string';
            case T_INLINE_HTML: return 'html';
            case T_COMMENT:
            case T_DOC_COMMENT: return 'comment';
            case T_ABSTRACT:
            case T_ARRAY:
            case T_ARRAY_CAST:
            case T_AS:
            case T_BOOLEAN_AND:
            case T_BOOLEAN_OR:
            case T_BOOL_CAST:
            case T_BREAK:
            case T_CASE:
            case T_CATCH:
            case T_CLASS:
            case T_CLONE:
            case T_CONCAT_EQUAL:
            case T_CONTINUE:
            case T_DEFAULT:
            case T_DOUBLE_ARROW:
            case T_DOUBLE_CAST:
            case T_ECHO:
            case T_ELSE:
            case T_ELSEIF:
            case T_EMPTY:
            case T_ENDDECLARE:
            case T_ENDFOR:
            case T_ENDFOREACH:
            case T_ENDIF:
            case T_ENDSWITCH:
            case T_ENDWHILE:
            case T_END_HEREDOC:
            case T_EXIT:
            case T_EXTENDS:
            case T_FINAL:
            case T_FOREACH:
            case T_FUNCTION:
            case T_GLOBAL:
            case T_IF:
            case T_INC:
            case T_INCLUDE:
            case T_INCLUDE_ONCE:
            case T_INSTANCEOF:
            case T_INT_CAST:
            case T_ISSET:
            case T_IS_EQUAL:
            case T_IS_IDENTICAL:
            case T_IS_NOT_IDENTICAL:
            case T_IS_SMALLER_OR_EQUAL:
            case T_NEW:
            case T_OBJECT_CAST:
            case T_OBJECT_OPERATOR:
            case T_PAAMAYIM_NEKUDOTAYIM:
            case T_PRIVATE:
            case T_PROTECTED:
            case T_PUBLIC:
            case T_REQUIRE:
            case T_REQUIRE_ONCE:
            case T_RETURN:
            case T_SL:
            case T_SL_EQUAL:
            case T_SR:
            case T_SR_EQUAL:
            case T_START_HEREDOC:
            case T_STATIC:
            case T_STRING_CAST:
            case T_THROW:
            case T_TRY:
            case T_UNSET_CAST:
            case T_VAR:
            case T_WHILE: return 'keyword';
            case T_CLOSE_TAG:
            case T_OPEN_TAG:
            case T_OPEN_TAG_WITH_ECHO:
            default: return 'default';
        }
    }
}
?>
