<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    CVS: $Id: Renderer.php,v 1.8.2.7 2006/02/25 17:02:23 sebastian Exp $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

/**
 * Abstract base class for Code Coverage renderers.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.1.0
 * @abstract
 */
abstract class PHPUnit2_Util_CodeCoverage_Renderer {
    /**
     * @var    array
     * @access protected
     */
    protected $codeCoverageInformation;

    /**
     * Constructor.
     *
     * @param  array $codeCoverageInformation
     * @access protected
     */
    protected function __construct($codeCoverageInformation) {
        $this->codeCoverageInformation = $codeCoverageInformation;
    }

    /**
     * Abstract Factory.
     *
     * @param  string  $rendererName
     * @param  array   $codeCoverageInformation
     * @throws Exception
     * @access public
     */
    public function factory($rendererName, $codeCoverageInformation) {
        require_once 'PHPUnit2/Util/CodeCoverage/Renderer/' . $rendererName . '.php';

        $class = 'PHPUnit2_Util_CodeCoverage_Renderer_' . $rendererName;
        return new $class($codeCoverageInformation);
    }

    /**
     * Visualizes the result array of
     * PHPUnit2_Framework_TestResult::getCodeCoverageInformation().
     *
     * @return string
     * @access public
     * @final
     */
    public final function render() {
        $buffer = $this->header();

        foreach ($this->getSummary() as $sourceFile => $executedLines) {
            if (file_exists($sourceFile)) {
                $buffer .= $this->startSourceFile($sourceFile);
                $buffer .= $this->renderSourceFile(file($sourceFile), $executedLines);
                $buffer .= $this->endSourceFile($sourceFile);
            }
        }

        return $buffer . $this->footer();
    }

    /**
     * Visualizes the result array of
     * PHPUnit2_Framework_TestResult::getCodeCoverageInformation()
     * and writes it to a file.
     *
     * @param  string $filename
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function renderToFile($filename) {
        if ($fp = fopen($filename, 'w')) {
            fputs(
              $fp,
              $this->render()
            );

            fclose($fp);
        }
    }

    /**
     * Returns summarized Code Coverage data.
     *
     * Format of the result array:
     *
     * <code>
     * array(
     *   "/tested/code.php" => array(
     *     linenumber => flag
     *   )
     * )
     * </code>
     *
     * flag > 0: line was executed.
     * flag < 0: line is executable but was not executed.
     *
     * @return array
     * @access protected
     * @since  Method available since Release 2.2.0
     */
    protected function getSummary() {
        $summary = array();

        foreach ($this->codeCoverageInformation as $testCaseName => $sourceFiles) {
            foreach ($sourceFiles as $sourceFile => $executedLines) {
                foreach ($executedLines as $lineNumber => $flag) {
                    if (!isset($summary[$sourceFile][$lineNumber])) {
                        $summary[$sourceFile][$lineNumber] = $flag;
                    }

                    else if ($flag > 0) {
                        $summary[$sourceFile][$lineNumber] = $flag;
                    }
                }
            }
        }

        return $summary;
    }

    /**
     * @return string
     * @access protected
     * @since  Method available since Release 2.1.1
     */
    protected function header() {
    }

    /**
     * @return string
     * @access protected
     * @since  Method available since Release 2.1.1
     */
    protected function footer() {
    }

    /**
     * @param  string $sourceFile
     * @return string
     * @access protected
     */
    protected function startSourceFile($sourceFile) {
    }

    /**
     * @param  string $sourceFile
     * @return string
     * @access protected
     */
    protected function endSourceFile($sourceFile) {
    }

    /**
     * @param  array $codeLines
     * @param  array $executedLines
     * @return string
     * @access protected
     * @abstract
     */
    abstract protected function renderSourceFile($codeLines, $executedLines);
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
