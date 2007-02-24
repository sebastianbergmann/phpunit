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
 * @version    CVS: $Id: HTML.php,v 1.7.2.3 2005/12/17 16:04:58 sebastian Exp $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit2/Util/CodeCoverage/Renderer.php';

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
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.1.0
 */
class PHPUnit2_Util_CodeCoverage_Renderer_HTML extends PHPUnit2_Util_CodeCoverage_Renderer {
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

    /**
     * @return string
     * @access protected
     * @since  Method available since Release 2.1.1
     */
    protected function header() {
        return self::pageHeader;
    }

    /**
     * @return string
     * @access protected
     * @since  Method available since Release 2.1.1
     */
    protected function footer() {
        return self::pageFooter;
    }

    /**
     * @param  string $sourceFile
     * @return string
     * @access protected
     */
    protected function startSourceFile($sourceFile) {
        return self::sourceFileHeader;
    }

    /**
     * @param  string $sourceFile
     * @return string
     * @access protected
     */
    protected function endSourceFile($sourceFile) {
        return self::sourceFileFooter;
    }

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
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
