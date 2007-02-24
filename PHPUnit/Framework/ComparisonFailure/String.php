<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Thrown when an assertion for string equality failed.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_ComparisonFailure_String extends PHPUnit_Framework_ComparisonFailure
{
    /**
     * Returns a string describing the difference between
     * the expected and the actual string value.
     */
    public function toString()
    {
        $expected = (string)$this->expected;
        $actual   = (string)$this->actual;

        if (substr(php_uname('s'), 0, 7) != 'Windows' &&
           (strpos($expected, "\n") !== FALSE ||
            strpos($actual, "\n")   !== FALSE)) {
            return $this->diff($expected, $actual);
        }

        $expectedLen = strlen($expected);
        $actualLen   = strlen($actual);
        $minLen      = min($expectedLen, $actualLen);
        $maxLen      = max($expectedLen, $actualLen);

        for ($i = 0; $i < $minLen; ++$i) {
            if ($expected[$i] != $actual[$i]) break;
        }

        $startPos = $i;
        $endPos   = $minLen;

        if ($minLen > 0) {
            for ($i = $minLen - 1; $i > $startPos; --$i) {
                if ($expected[$i] != $actual[$i]) break;
            }

            $endPos = $i + 1;
        }

        return sprintf(
          "%s%sexpected string <%s>\n" .
          "%sdifference      <%s>\n" .
          '%sgot string      <%s>',

          $this->message,
          ($this->message != '') ? ' ' : '',
          $expected,
          ($this->message != '') ? str_repeat(' ', strlen($this->message) + 1) : '',
          str_repeat(' ', $startPos) . str_repeat('x', $endPos - $startPos) . str_repeat('?', $maxLen - $minLen),
          ($this->message != '') ? str_repeat(' ', strlen($this->message) + 1) : '',
          $actual
        );
    }

    private function diff($expected, $actual)
    {
        $expectedFile = tempnam('/tmp', 'expected');
        file_put_contents($expectedFile, $expected);

        $actualFile = tempnam('/tmp', 'actual');
        file_put_contents($actualFile, $actual);

        $buffer = explode(
          "\n",
          shell_exec(
            sprintf(
              'diff -u %s %s',
              $expectedFile,
              $actualFile
            )
          )
        );

        unlink($expectedFile);
        unlink($actualFile);

        $buffer[0] = "--- Expected";
        $buffer[1] = "+++ Actual";

        return implode("\n", $buffer);
    }
}
?>
