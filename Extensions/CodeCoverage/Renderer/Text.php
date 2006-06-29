<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: Text.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Extensions/CodeCoverage/Renderer.php';

/**
 * Renders Code Coverage information in text format.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  Extensions
 * @since       2.1.0
 */
class PHPUnit2_Extensions_CodeCoverage_Renderer_Text extends PHPUnit2_Extensions_CodeCoverage_Renderer {
    // {{{ protected function startTestCase($testCaseName)

    /**
    * @param  string $testCaseName
    * @return string
    * @access protected
    */
    protected function startTestCase($testCaseName) {
        return $testCaseName . "\n\n";
    }

    // }}}
    // {{{ protected function endTestCase($testCaseName)

    /**
    * @param  string $testCaseName
    * @return string
    * @access protected
    */
    protected function endTestCase($testCaseName) {
        return "\n";
    }

    // }}}
    // {{{ protected function startSourceFile($sourceFile)

    /**
    * @param  string $sourceFile
    * @return string
    * @access protected
    */
    protected function startSourceFile($sourceFile) {
        return '  ' . $sourceFile . "\n\n";
    }

    // }}}
    // {{{ protected function endSourceFile($sourceFile)

    /**
    * @param  string $sourceFile
    * @return string
    * @access protected
    */
    protected function endSourceFile($sourceFile) {
        return "\n";
    }

    // }}}
    // {{{ protected function renderSourceFile($codeLines, $executedLines)

    /**
    * @param  array $codeLines
    * @param  array $executedLines
    * @return string
    * @access protected
    */
    protected function renderSourceFile($codeLines, $executedLines) {
        $buffer = '';
        $line   = 1;

        foreach ($codeLines as $codeLine) {
            $buffer .= sprintf(
              '    %4u|%4s| %s',

              $line,
              (isset($executedLines[$line])) ? $executedLines[$line] . 'x' : '',
              $codeLine
            );

            $line++;
        }

        return $buffer;
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
