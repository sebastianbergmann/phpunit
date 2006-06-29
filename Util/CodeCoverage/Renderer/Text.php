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
 * @version    CVS: $Id: Text.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit2/Util/CodeCoverage/Renderer.php';

/**
 * Renders Code Coverage information in text format.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.1.0
 */
class PHPUnit2_Util_CodeCoverage_Renderer_Text extends PHPUnit2_Util_CodeCoverage_Renderer {
    /**
     * @param  string $sourceFile
     * @return string
     * @access protected
     */
    protected function startSourceFile($sourceFile) {
        return '  ' . $sourceFile . "\n\n";
    }

    /**
     * @param  string $sourceFile
     * @return string
     * @access protected
     */
    protected function endSourceFile($sourceFile) {
        return "\n";
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
            $flag = '-';

            if (isset($executedLines[$line])) {
                if ($executedLines[$line] > 0) {
                    $flag = '+';
                } else {
                    $flag = '#';
                }
            }

            $buffer .= sprintf(
              '    %4u|%s| %s',

              $line,
              $flag,
              $codeLine
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
