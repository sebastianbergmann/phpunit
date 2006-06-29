<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Renderer.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

/**
 * Abstract base class for Code Coverage renderers.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
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
     * flag > 1: line was executed.
     * flag < 1: line is executable but was not executed.
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
