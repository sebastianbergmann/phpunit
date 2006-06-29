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
    // {{{ Members

    /**
    * @var    array
    * @access protected
    */
    protected $codeCoverageInformation;

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
    // {{{ public function factory($type, $codeCoverageInformation)

    /**
    * Abstract Factory.
    *
    * @param  string  $type
    * @param  array   $codeCoverageInformation
    * @access public
    */
    public function factory($type, $codeCoverageInformation) {
        $class  = 'PHPUnit2_Extensions_CodeCoverage_Renderer_' . $type;

        if (@require_once('PHPUnit2/Extensions/CodeCoverage/Renderer/' . $type . '.php')) {
            $object = new $class($codeCoverageInformation);

            return $object;
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
    // {{{ public function render()

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

        foreach ($this->codeCoverageInformation as $testCaseName => $sourceFiles) {
            $buffer .= $this->startTestCase($testCaseName);

            foreach ($sourceFiles as $sourceFile => $executedLines) {
                $buffer .= $this->startSourceFile($sourceFile);
                $buffer .= $this->renderSourceFile(file($sourceFile), $executedLines);
                $buffer .= $this->endSourceFile($sourceFile);
            }

            $buffer .= $this->endTestCase($testCaseName);
        }

        return $buffer . $this->footer();
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
    // {{{ protected function startTestCase($testCaseName)

    /**
    * @param  string $testCaseName
    * @return string
    * @access protected
    */
    protected function startTestCase($testCaseName) {
    }

    // }}}
    // {{{ protected function endTestCase($testCaseName)

    /**
    * @param  string $testCaseName
    * @return string
    * @access protected
    */
    protected function endTestCase($testCaseName) {
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
