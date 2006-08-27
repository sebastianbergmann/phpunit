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
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
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
    protected $coveringTests = array();

    /**
     * @var    array
     * @access protected
     */
    protected $executedLines;

    /**
     * @var    integer
     * @access protected
     */
    protected $numExecutedLines = -1;

    /**
     * Constructor.
     *
     * @param  string                          $name
     * @param  PHPUnit_Util_CodeCoverage_Node $parent
     * @param  array                           $lines
     * @throws RuntimeException
     * @access public
     */
    public function __construct($name, PHPUnit_Util_Report_Coverage_Node $parent, Array $executedLines)
    {
        parent::__construct($name, $parent);

        $path = $this->getPath();

        if (!file_exists($path)) {
            throw new RuntimeException;
        }

        $this->codeLines     = file($path);
        $this->executedLines = $executedLines;

        $this->cleanupExecutableLines();
    }

    /**
     * @access protected
     */
    protected function cleanupExecutableLines()
    {
        if (!function_exists('token_get_all')) {
            return;
        }

        $inComment = FALSE;
        $i         = 1;

        foreach ($this->codeLines as $line) {
            $line = trim($line);

            switch ($line) {
                case '{':
                case '}': {
                    $this->executedLines[$i] = 1;

                    $i++;
                    continue 2;
                }
                break;

                case '<?':
                case '<?php':
                case '?>': {
                    if (isset($this->executedLines[$i])) {
                        unset($this->executedLines[$i]);
                    }

                    $i++;
                    continue 2;
                }
            }

            if (strpos($line, '*/') === 0) {
                $inComment = FALSE;

                if (isset($this->executedLines[$i])) {
                    unset($this->executedLines[$i]);
                }

                $i++;
                continue;
            }

            if ($inComment || (strpos($line, '//') === 0)) {
                if (isset($this->executedLines[$i])) {
                    unset($this->executedLines[$i]);
                }

                $i++;
                continue;
            }

            if (strpos($line, '/*') === 0) {
                $inComment = TRUE;

                if (isset($this->executedLines[$i])) {
                    unset($this->executedLines[$i]);
                }

                $i++;
                continue;
            }

            $tokens = token_get_all('<?php ' . $line . ' ?>');

            foreach ($tokens as $token) {
                if (is_string($token)) {
                    $i++;
                    continue 2;
                }

                switch ($token[0]) {
                    case T_PRIVATE:
                    case T_PUBLIC:
                    case T_PROTECTED:
                    case T_FUNCTION:
                    case T_CLASS:
                    case T_REQUIRE:
                    case T_REQUIRE_ONCE:
                    case T_INCLUDE:
                    case T_INCLUDE_ONCE: {
                        $this->executedLines[$i] = 1;
                    }
                }
            }

            $i++;
        }
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

        foreach ($files as $file => $lines) {
            if ($thisName == $file) {
                foreach ($lines as $line => $tests) {
                    foreach ($tests as $test) {
                        $testId = $test->__testNode->testId;

                        if (!isset($testCase[$testId])) {
                            $testCase[$testId] = array('numLinesExecuted' => 1, 'object' => $test);
                        } else {
                            $testCase[$testId]['numLinesExecuted']++;
                        }
                    }
                }
            }
        }

        foreach ($testCase as $coveringTest) {
            $test = $coveringTest['object'];
            $test = $testSuite->lookupTest($test);
            $name = $test->getName(TRUE);

            if (!isset($this->coveringTests[$name[0]])) {
                $this->coveringTests[$name[0]] = array();
            }

            $found = FALSE;

            foreach ($this->coveringTests[$name[0]] as $_name => $coveringTest) {
                if ($coveringTest['object'] === $test) {
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
        return count($this->executedLines);
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

            foreach ($this->executedLines as $line) {
                if (count($line) > 0) {
                    $this->numExecutedLines++;
                }
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
        $template = new PHPUnit_Util_Template(
          PHPUnit_Util_Report::getTemplatePath() .
          'coverage_file.html'
        );

        $i     = 1;
        $lines = '';

        foreach ($this->codeLines as $line) {
            $css = '';

            if (isset($this->executedLines[$i])) {
                $css = sprintf(
                  '<span class="%s">       %8d : ',

                  count($this->executedLines[$i]) > 0 ? 'lineCov' : 'lineNoCov',
                  count($this->executedLines[$i])
                );
            }

            $lines .= sprintf(
              '<span class="lineNum">%8d </span>%s%s%s' . "\n",

              $i,
              !empty($css) ? $css : '                : ',
              htmlspecialchars(rtrim($line)),
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

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
