<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/File.php';
require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/Template.php';
require_once 'PHPUnit/Util/Report/Node.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Util_Report_Node_File extends PHPUnit_Util_Report_Node
{
    /**
     * @var    array
     */
    protected $codeLines;

    /**
     * @var    array
     */
    protected $codeLinesFillup = array();

    /**
     * @var    array
     */
    protected $executedLines;

    /**
     * @var    boolean
     */
    protected $yui = TRUE;

    /**
     * @var    boolean
     */
    protected $highlight = FALSE;

    /**
     * @var    integer
     */
    protected $numExecutableLines = 0;

    /**
     * @var    integer
     */
    protected $numExecutedLines = 0;

    /**
     * @var    array
     */
    protected $classes = array();

    /**
     * @var    integer
     */
    protected $numClasses = 0;

    /**
     * @var    integer
     */
    protected $numTestedClasses = 0;

    /**
     * @var    integer
     */
    protected $numMethods = 0;

    /**
     * @var    integer
     */
    protected $numTestedMethods = 0;

    /**
     * @var    string
     */
    protected $yuiPanelJS = '';

    /**
     * @var    array
     */
    protected $startLines = array();

    /**
     * @var    array
     */
    protected $endLines = array();

    /**
     * Constructor.
     *
     * @param  string                   $name
     * @param  PHPUnit_Util_Report_Node $parent
     * @param  array                    $executedLines
     * @param  boolean                  $yui
     * @param  boolean                  $highlight
     * @throws RuntimeException
     */
    public function __construct($name, PHPUnit_Util_Report_Node $parent = NULL, array $executedLines, $yui = TRUE, $highlight = FALSE)
    {
        parent::__construct($name, $parent);

        $path = $this->getPath();

        if (!file_exists($path)) {
            throw new RuntimeException;
        }

        $this->executedLines = $executedLines;
        $this->highlight     = $highlight;
        $this->yui           = $yui;
        $this->codeLines     = $this->loadFile($path);

        $this->calculateStatistics();
    }

    /**
     * Returns the classes of this node.
     *
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Returns the number of executable lines.
     *
     * @return integer
     */
    public function getNumExecutableLines()
    {
        return $this->numExecutableLines;
    }

    /**
     * Returns the number of executed lines.
     *
     * @return integer
     */
    public function getNumExecutedLines()
    {
        return $this->numExecutedLines;
    }

    /**
     * Returns the number of classes.
     *
     * @return integer
     */
    public function getNumClasses()
    {
        return $this->numClasses;
    }

    /**
     * Returns the number of tested classes.
     *
     * @return integer
     */
    public function getNumTestedClasses()
    {
        return $this->numTestedClasses;
    }

    /**
     * Returns the number of methods.
     *
     * @return integer
     */
    public function getNumMethods()
    {
        return $this->numMethods;
    }

    /**
     * Returns the number of tested methods.
     *
     * @return integer
     */
    public function getNumTestedMethods()
    {
        return $this->numTestedMethods;
    }

    /**
     * Renders this node.
     *
     * @param string  $target
     * @param string  $title
     * @param string  $charset
     * @param integer $lowUpperBound
     * @param integer $highLowerBound
     */
    public function render($target, $title, $charset = 'ISO-8859-1', $lowUpperBound = 35, $highLowerBound = 70)
    {
        if ($this->yui) {
            $template = new PHPUnit_Util_Template(
              PHPUnit_Util_Report::$templatePath . 'file.html'
            );

            $yuiTemplate = new PHPUnit_Util_Template(
              PHPUnit_Util_Report::$templatePath . 'yui_item.js'
            );
        } else {
            $template = new PHPUnit_Util_Template(
              PHPUnit_Util_Report::$templatePath . 'file_no_yui.html'
            );
        }

        $i      = 1;
        $lines  = '';
        $ignore = FALSE;

        foreach ($this->codeLines as $line) {
            if (strpos($line, '@codeCoverageIgnore') !== FALSE) {
                if (strpos($line, '@codeCoverageIgnoreStart') !== FALSE) {
                    $ignore = TRUE;
                }

                else if (strpos($line, '@codeCoverageIgnoreEnd') !== FALSE) {
                    $ignore = FALSE;
                }
            }

            $css = '';

            if (!$ignore && isset($this->executedLines[$i])) {
                $count = '';

                // Array: Line is executable and was executed.
                // count(Array) = Number of tests that hit this line.
                if (is_array($this->executedLines[$i])) {
                    $color    = 'lineCov';
                    $numTests = count($this->executedLines[$i]);
                    $count    = sprintf('%8d', $numTests);

                    if ($this->yui) {
                        $buffer  = '';
                        $testCSS = '';

                        foreach ($this->executedLines[$i] as $test) {
                            if (!isset($test->__liHtml)) {
                                $test->__liHtml = '';

                                if ($test instanceof PHPUnit_Framework_SelfDescribing) {
                                    $testName = $test->toString();

                                    if ($test instanceof PHPUnit_Framework_TestCase) {
                                        switch ($test->getStatus()) {
                                            case PHPUnit_Runner_BaseTestRunner::STATUS_PASSED: {
                                                $testCSS = ' class=\"testPassed\"';
                                            }
                                            break;

                                            case PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE: {
                                                $testCSS = ' class=\"testFailure\"';
                                            }
                                            break;

                                            case PHPUnit_Runner_BaseTestRunner::STATUS_ERROR: {
                                                $testCSS = ' class=\"testError\"';
                                            }
                                            break;

                                            case PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE:
                                            case PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED: {
                                                $testCSS = ' class=\"testIncomplete\"';
                                            }
                                            break;

                                            default: {
                                                $testCSS = '';
                                            }
                                        }
                                    }
                                }

                                $test->__liHtml .= sprintf(
                                  '<li%s>%s</li>',

                                  $testCSS,
                                  addslashes(htmlspecialchars($testName))
                                );
                            }

                            $buffer .= $test->__liHtml;
                        }

                        if ($numTests > 1) {
                            $header = $numTests . ' tests cover';
                        } else {
                            $header = '1 test covers';
                        }

                        $header .= ' line ' . $i;

                        $yuiTemplate->setVar(
                          array(
                            'line'   => $i,
                            'header' => $header,
                            'tests'  => $buffer
                          ),
                          FALSE
                        );

                        $this->yuiPanelJS .= $yuiTemplate->render();
                    }
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

            $fillup = array_shift($this->codeLinesFillup);

            if ($fillup > 0) {
                $line .= str_repeat(' ', $fillup);
            }

            $lines .= sprintf(
              '<span class="lineNum" id="container%d"><a name="%d"></a><a href="#%d" id="line%d">%8d</a> </span>%s%s%s' . "\n",

              $i,
              $i,
              $i,
              $i,
              $i,
              !empty($css) ? $css : '                : ',
              !$this->highlight ? htmlspecialchars($line) : $line,
              !empty($css) ? '</span>' : ''
            );

            $i++;
        }

        $items = '';

        foreach ($this->classes as $className => $classData) {
            if ($classData['executedLines'] == $classData['executableLines']) {
                $numTestedClasses     = 1;
                $testedClassesPercent = 100;
            } else {
                $numTestedClasses     = 0;
                $testedClassesPercent = 0;
            }

            $numTestedMethods = 0;
            $numMethods       = count($classData['methods']);

            foreach ($classData['methods'] as $method) {
                if ($method['executedLines'] == $method['executableLines']) {
                    $numTestedMethods++;
                }
            }

            $items .= $this->doRenderItem(
              array(
                'name'                 => sprintf(
                  '<b><a href="#%d">%s</a></b>',

                  $classData['startLine'],
                  $className
                ),
                'numClasses'           => 1,
                'numTestedClasses'     => $numTestedClasses,
                'testedClassesPercent' => sprintf('%01.2f', $testedClassesPercent),
                'numMethods'           => $numMethods,
                'numTestedMethods'     => $numTestedMethods,
                'testedMethodsPercent' => $this->calculatePercent(
                  $numTestedMethods, $numMethods
                ),
                'numExecutableLines'   => $classData['executableLines'],
                'numExecutedLines'     => $classData['executedLines'],
                'executedLinesPercent' => $this->calculatePercent(
                  $classData['executedLines'], $classData['executableLines']
                )
              ),
              $lowUpperBound,
              $highLowerBound
            );

            foreach ($classData['methods'] as $methodName => $methodData) {
                if ($methodData['executedLines'] == $methodData['executableLines']) {
                    $numTestedMethods     = 1;
                    $testedMethodsPercent = 100;
                } else {
                    $numTestedMethods     = 0;
                    $testedMethodsPercent = 0;
                }

                $items .= $this->doRenderItem(
                  array(
                    'name'                 => sprintf(
                      '&nbsp;<a href="#%d">%s</a>',

                      $methodData['startLine'],
                      $methodData['signature']
                    ),
                    'numClasses'           => '',
                    'numTestedClasses'     => '',
                    'testedClassesPercent' => '',
                    'numMethods'           => 1,
                    'numTestedMethods'     => $numTestedMethods,
                    'testedMethodsPercent' => sprintf('%01.2f', $testedMethodsPercent),
                    'numExecutableLines'   => $methodData['executableLines'],
                    'numExecutedLines'     => $methodData['executedLines'],
                    'executedLinesPercent' => $this->calculatePercent(
                      $methodData['executedLines'], $methodData['executableLines']
                    )
                  ),
                  $lowUpperBound,
                  $highLowerBound,
                  'method_item.html'
                );
            }
        }

        $this->setTemplateVars($template, $title, $charset);

        $template->setVar(
          array(
            'lines'      => $lines,
            'total_item' => $this->renderTotalItem($lowUpperBound, $highLowerBound, FALSE),
            'items'      => $items,
            'yuiPanelJS' => $this->yuiPanelJS
          )
        );

        $cleanId = PHPUnit_Util_Filesystem::getSafeFilename($this->getId());
        $template->renderTo($target . $cleanId . '.html');

        $this->yuiPanelJS    = '';
        $this->executedLines = array();
    }

    /**
     * Calculates coverage statistics for the file.
     *
     */
    protected function calculateStatistics()
    {
        $this->processClasses();
        $this->processFunctions();

        $ignoreStart = -1;
        $lineNumber  = 1;

        foreach ($this->codeLines as $line) {
            if (isset($this->startLines[$lineNumber])) {
                // Start line of a class.
                if (isset($this->startLines[$lineNumber]['methods'])) {
                    $currentClass = &$this->startLines[$lineNumber];
                }

                // Start line of a method.
                else {
                    $currentMethod = &$this->startLines[$lineNumber];
                }
            }

            if (strpos($line, '@codeCoverageIgnore') !== FALSE) {
                if (strpos($line, '@codeCoverageIgnoreStart') !== FALSE) {
                    $ignoreStart = $lineNumber;
                }

                else if (strpos($line, '@codeCoverageIgnoreEnd') !== FALSE) {
                    $ignoreStart = -1;
                }
            }

            if (isset($this->executedLines[$lineNumber])) {
                // Array: Line is executable and was executed.
                if (is_array($this->executedLines[$lineNumber])) {
                    if (isset($currentClass)) {
                        $currentClass['executableLines']++;
                        $currentClass['executedLines']++;
                    }

                    if (isset($currentMethod)) {
                        $currentMethod['executableLines']++;
                        $currentMethod['executedLines']++;
                    }

                    $this->numExecutableLines++;
                    $this->numExecutedLines++;
                }

                // -1: Line is executable and was not executed.
                else if ($this->executedLines[$lineNumber] == -1) {
                    if (isset($currentClass)) {
                        $currentClass['executableLines']++;
                    }

                    if (isset($currentMethod)) {
                        $currentMethod['executableLines']++;
                    }

                    $this->numExecutableLines++;

                    if ($ignoreStart != -1 && $lineNumber > $ignoreStart) {
                        if (isset($currentClass)) {
                            $currentClass['executedLines']++;
                        }

                        if (isset($currentMethod)) {
                            $currentMethod['executedLines']++;
                        }

                        $this->numExecutedLines++;
                    }
                }
            }

            if (isset($this->endLines[$lineNumber])) {
                // End line of a class.
                if (isset($this->endLines[$lineNumber]['methods'])) {
                    unset($currentClass);
                }

                // End line of a method.
                else {
                    unset($currentMethod);
                }
            }

            $lineNumber++;
        }

        foreach ($this->classes as $class) {
            foreach ($class['methods'] as $method) {
                if ($method['executedLines'] == $method['executableLines']) {
                    $this->numTestedMethods++;
                }
            }

            if ($class['executedLines'] == $class['executableLines']) {
                $this->numTestedClasses++;
            }
        }
    }

    /**
     * @author Aidan Lister <aidan@php.net>
     * @author Sebastian Bergmann <sb@sebastian-bergmann.de>
     * @param  string  $file
     * @return array
     */
    protected function loadFile($file)
    {
        $lines  = explode("\n", str_replace("\t", '    ', file_get_contents($file)));
        $result = array();

        if (count($lines) == 0) {
            return $result;
        }

        $lines       = array_map('rtrim', $lines);
        $linesLength = array_map('strlen', $lines);
        $width       = max($linesLength);

        foreach ($linesLength as $line => $length) {
            $this->codeLinesFillup[$line] = $width - $length;
        }

        if (!$this->highlight) {
            unset($lines[count($lines)-1]);
            return $lines;
        }

        $tokens     = token_get_all(file_get_contents($file));
        $stringFlag = FALSE;
        $i          = 0;
        $result[$i] = '';

        foreach ($tokens as $j => $token) {
            if (is_string($token)) {
                if ($token === '"' && $tokens[$j - 1] !== '\\') {
                    $result[$i] .= sprintf(
                      '<span class="string">%s</span>',

                      htmlspecialchars($token)
                    );

                    $stringFlag = !$stringFlag;
                } else {
                    $result[$i] .= sprintf(
                      '<span class="keyword">%s</span>',

                      htmlspecialchars($token)
                    );
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
                        if ($stringFlag) {
                            $colour = 'string';
                        } else {
                            switch ($token) {
                                case T_INLINE_HTML: {
                                    $colour = 'html';
                                }
                                break;

                                case T_COMMENT:
                                case T_DOC_COMMENT: {
                                    $colour = 'comment';
                                }
                                break;

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
                                case T_WHILE: {
                                    $colour = 'keyword';
                                }
                                break;

                                default: {
                                    $colour = 'default';
                                }
                            }
                        }

                        $result[$i] .= sprintf(
                          '<span class="%s">%s</span>',

                          $colour,
                          $line
                        );
                    }

                    if (isset($lines[$jj + 1])) {
                        $result[++$i] = '';
                    }
                }
            }
        }

        unset($result[count($result)-1]);

        return $result;
    }

    protected function processClasses()
    {
        $classes = PHPUnit_Util_File::getClassesInFile($this->getPath());

        foreach ($classes as $className => $class) {
            $this->classes[$className] = array(
              'methods'         => array(),
              'startLine'       => $class['startLine'],
              'executableLines' => 0,
              'executedLines'   => 0
            );

            $this->startLines[$class['startLine']] = &$this->classes[$className];
            $this->endLines[$class['endLine']]     = &$this->classes[$className];

            foreach ($class['methods'] as $methodName => $method) {
                $this->classes[$className]['methods'][$methodName] = array(
                  'signature'       => $method['signature'],
                  'startLine'       => $method['startLine'],
                  'executableLines' => 0,
                  'executedLines'   => 0
                );

                $this->startLines[$method['startLine']] = &$this->classes[$className]['methods'][$methodName];
                $this->endLines[$method['endLine']]     = &$this->classes[$className]['methods'][$methodName];

                $this->numMethods++;
            }

            $this->numClasses++;
        }
    }

    protected function processFunctions()
    {
        $functions = PHPUnit_Util_File::getFunctionsInFile($this->getPath());

        if (count($functions) > 0 && !isset($this->classes['*'])) {
            $this->classes['*'] = array(
              'methods'         => array(),
              'startLine'       => 0,
              'executableLines' => 0,
              'executedLines'   => 0
            );
        }

        foreach ($functions as $functionName => $function) {
            $this->classes['*']['methods'][$functionName] = array(
              'signature'       => $function['signature'],
              'startLine'       => $function['startLine'],
              'executableLines' => 0,
              'executedLines'   => 0
            );

            $this->startLines[$function['startLine']] = &$this->classes['*']['methods'][$functionName];
            $this->endLines[$function['endLine']]     = &$this->classes['*']['methods'][$functionName];

            $this->numMethods++;
        }
    }
}
?>
