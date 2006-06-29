<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: Renderer.php 539 2006-02-13 16:08:42Z sb $
//

/**
 * Abstract base class for Code Coverage renderers.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 * @since       2.1.0
 * @abstract
 */
abstract class PHPUnit2_Extensions_CodeCoverage_Renderer {
    // {{{ Instance Variables

    /**
    * @var    array
    * @access protected
    */
    protected $codeCoverageInformation;

    /**
    * @var    boolean
    * @access protected
    */
    protected $useSummary = TRUE;

    // }}}
    // {{{ protected function __construct($codeCoverageInformation)

    /**
    * Constructor.
    *
    * @param  array $codeCoverageInformation
    * @access protected
    */
    protected function __construct($codeCoverageInformation) {
        $this->codeCoverageInformation = $codeCoverageInformation;
    }

    // }}}
    // {{{ public function factory($rendererName, $codeCoverageInformation)

    /**
    * Abstract Factory.
    *
    * @param  string  $rendererName
    * @param  array   $codeCoverageInformation
    * @access public
    */
    public function factory($rendererName, $codeCoverageInformation) {
        $class = 'PHPUnit2_Extensions_CodeCoverage_Renderer_' . $rendererName;

        @require_once 'PHPUnit2/Extensions/CodeCoverage/Renderer/' . $rendererName . '.php';

        if (class_exists($class)) {
            return new $class($codeCoverageInformation);
        } else {
            throw new Exception(
              sprintf(
                'Could not load class %s.',
                $class
              )
            );
        }
    }

    // }}}
    // {{{ public final function render()

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

        if ($this->useSummary) {
            foreach ($this->getSummary() as $sourceFile => $executedLines) {
                $buffer .= $this->startSourceFile($sourceFile);
                $buffer .= $this->renderSourceFile(file($sourceFile), $executedLines);
                $buffer .= $this->endSourceFile($sourceFile);
            }
        } else {
        }

        return $buffer . $this->footer();
    }

    // }}}
    // {{{ public function renderToFile($filename)

    /**
    * Visualizes the result array of
    * PHPUnit2_Framework_TestResult::getCodeCoverageInformation()
    * and writes it to a file.
    *
    * @param  string $filename
    * @access public
    * @since  2.2.0
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

    // }}}
    // {{{ protected function getSummary()

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
    * @since  2.2.0
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

    // }}}
    // {{{ protected function header()

    /**
    * @return string
    * @access protected
    * @since  2.1.1
    */
    protected function header() {
    }

    // }}}
    // {{{ protected function footer()

    /**
    * @return string
    * @access protected
    * @since  2.1.1
    */
    protected function footer() {
    }

    // }}}
    // {{{ protected function startSourceFile($sourceFile)

    /**
    * @param  string $sourceFile
    * @return string
    * @access protected
    */
    protected function startSourceFile($sourceFile) {
    }

    // }}}
    // {{{ protected function endSourceFile($sourceFile)

    /**
    * @param  string $sourceFile
    * @return string
    * @access protected
    */
    protected function endSourceFile($sourceFile) {
    }

    // }}}
    // {{{ abstract protected function renderSourceFile($codeLines, $executedLines)

    /**
    * @param  array $codeLines
    * @param  array $executedLines
    * @return string
    * @access protected
    * @abstract
    */
    abstract protected function renderSourceFile($codeLines, $executedLines);

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
