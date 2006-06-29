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
// $Id: HTML.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Extensions/CodeCoverage/Renderer.php';

/**
 * Renders Code Coverage information in HTML format.
 *
 * Formatting of the generated HTML can be achieved through
 * CSS (codecoverage.css).
 *
 * Example
 *
 * <code>
 * td.ccLineNumber, td.ccCoveredLine, td.ccUncoveredLine, td.ccNotExecutableLine {
 *   font-family: monospace;
 *   white-space: pre;
 * }
 *
 * td.ccLineNumber, td.ccCoveredLine {
 *   background-color: #aaaaaa;
 * }
 *
 * td.ccNotExecutableLine {
 *   color: #aaaaaa;
 * }
 * </code>
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 * @since       2.1.0
 */
class PHPUnit2_Extensions_CodeCoverage_Renderer_HTML extends PHPUnit2_Extensions_CodeCoverage_Renderer {
    // {{{ Constants

    const pageHeader =
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <link href="codecoverage.css" type="text/css" rel="stylesheet" />
  </head>
  <body>
';

    const pageFooter =
'  </body>
</html>
';

    const sourceFileHeader =
'   <table style="border: 1px solid black" cellspacing="0" cellpadding="0" width="100%">
';

    const sourceFileFooter =
'   </table>
';

    const codeLine =
'     <tr><td class="ccLineNumber">%s</td><td class="%s">%s</td></tr>
';

    // }}}
    // {{{ protected function header()

    /**
    * @return string
    * @access protected
    * @since  2.1.1
    */
    protected function header() {
        return self::pageHeader;
    }

    // }}}
    // {{{ protected function footer()

    /**
    * @return string
    * @access protected
    * @since  2.1.1
    */
    protected function footer() {
        return self::pageFooter;
    }

    // }}}
    // {{{ protected function startSourceFile($sourceFile)

    /**
    * @param  string $sourceFile
    * @return string
    * @access protected
    */
    protected function startSourceFile($sourceFile) {
        return self::sourceFileHeader;
    }

    // }}}
    // {{{ protected function endSourceFile($sourceFile)

    /**
    * @param  string $sourceFile
    * @return string
    * @access protected
    */
    protected function endSourceFile($sourceFile) {
        return self::sourceFileFooter;
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
            $lineStyle = 'ccNotExecutableLine';

            if (isset($executedLines[$line])) {
                if ($executedLines[$line] > 0) {
                    $lineStyle = 'ccCoveredLine';
                } else {
                    $lineStyle = 'ccUncoveredLine';
                }
            }

            $buffer .= sprintf(
              self::codeLine,

              $line,
              $lineStyle,
              htmlspecialchars(rtrim($codeLine))
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
