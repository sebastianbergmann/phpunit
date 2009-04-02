<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Type.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

if (!class_exists('PHPUnit_Framework_ComparisonFailure', FALSE)) {

/**
 * Thrown when an assertion for string equality failed.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
abstract class PHPUnit_Framework_ComparisonFailure extends PHPUnit_Framework_AssertionFailedError
{
    /**
     * Expected value of the retrieval which does not match $actual.
     * @var mixed
     */
    protected $expected;

    /**
     * Actually retrieved value which does not match $expected.
     * @var mixed
     */
    protected $actual;

    /**
     * @var boolean
     */
    protected $identical;

    /**
     * Optional message which is placed in front of the first line
     * returned by toString().
     * @var string
     */
    protected $message;

    /**
     * @var boolean
     */
    protected static $hasDiff = NULL;

    /**
     * Initialises with the expected value and the actual value.
     *
     * @param mixed $expected Expected value retrieved.
     * @param mixed $actual Actual value retrieved.
     * @param boolean $identical
     * @param string $message A string which is prefixed on all returned lines
     *                       in the difference output.
     */
    public function __construct($expected, $actual, $identical = FALSE, $message = '')
    {
        $this->expected  = $expected;
        $this->actual    = $actual;
        $this->identical = $identical;
        $this->message   = $message;
    }

    public function getActual()
    {
        return $this->actual;
    }

    public function getExpected()
    {
        return $this->expected;
    }

    public function identical()
    {
        return $this->identical;
    }

    /**
     * Figures out which diff class to use for the input types then
     * instantiates that class and returns the object.
     * @note The diff is type sensitive, if the type differs only the types
     *       are shown.
     *
     * @param mixed $expected Expected value retrieved.
     * @param mixed $actual Actual value retrieved.
     * @param string $message A string which is prefixed on all returned lines
     *                       in the difference output.
     * @return PHPUnit_Framework_ComparisonFailure
     */
    public static function diffIdentical($expected, $actual, $message = '')
    {
        if (gettype($expected) !== gettype($actual)) {
            return new PHPUnit_Framework_ComparisonFailure_Type($expected, $actual, TRUE, $message);
        }

        elseif (is_string($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_String($expected, $actual, TRUE, $message);
        }

        elseif (is_null($expected) || is_scalar($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Scalar($expected, $actual, TRUE, $message);
        }

        elseif (is_array($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Array($expected, $actual, TRUE, $message);
        }

        elseif (is_object($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Object($expected, $actual, TRUE, $message);
        }
    }

    /**
     * Figures out which diff class to use for the input types then
     * instantiates that class and returns the object.
     * @note The diff is not type sensitive, if the type differs the $actual
     *       value will be converted to the same type as the $expected.
     *
     * @param mixed $expected Expected value retrieved.
     * @param mixed $actual Actual value retrieved.
     * @param string $message A string which is prefixed on all returned lines
     *                       in the difference output.
     * @return PHPUnit_Framework_ComparisonFailure
     */
    public static function diffEqual($expected, $actual, $message = '')
    {
        if (is_string($expected) && !is_object($actual)) {
            return new PHPUnit_Framework_ComparisonFailure_String($expected, $actual, FALSE, $message);
        }

        elseif (is_null($expected) || is_scalar($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Scalar($expected, $actual, FALSE, $message);
        }

        elseif (is_array($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Array($expected, $actual, FALSE, $message);
        }

        elseif (is_object($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Object($expected, $actual, FALSE, $message);
        }
    }

    protected function diff($expected, $actual)
    {
        if (defined('PHPUNIT_TMPDIR')) {
            $tmpDir = PHPUNIT_TMPDIR;
        }

        else if (function_exists('sys_get_temp_dir')) {
            $tmpDir = sys_get_temp_dir();
        }

        else {
            $tmpDir = '/tmp';
        }

        $expectedFile = tempnam($tmpDir, 'expected');
        $actualFile   = tempnam($tmpDir, 'actual');

        file_put_contents($expectedFile, $expected);
        file_put_contents($actualFile, $actual);

        $buffer = shell_exec(
          sprintf(
            'diff -u %s %s',
            escapeshellarg($expectedFile),
            escapeshellarg($actualFile)
          )
        );

        unlink($expectedFile);
        unlink($actualFile);

        if (!empty($buffer)) {
            $buffer = explode("\n", $buffer);

            $buffer[0] = "--- Expected";
            $buffer[1] = "+++ Actual";

            $buffer = implode("\n", $buffer);
        }

        return $buffer;
    }

    public static function hasDiff()
    {
        if (self::$hasDiff === NULL)
        {
            self::$hasDiff = FALSE;

            $binary = 'diff';

            if (substr(php_uname('s'), 0, 7) == 'Windows')
            {
                $binary .= '.exe';
            }

            if (isset($_ENV['PATH'])) {
                $var = $_ENV['PATH'];
            }

            else if (isset($_ENV['Path'])) {
                $var = $_ENV['Path'];
            }

            else if (isset($_SERVER['PATH'])) {
                $var = $_SERVER['PATH'];
            }

            else if (isset($_SERVER['Path'])) {
                $var = $_SERVER['Path'];
            }

            if (isset($var)) {
                $paths = explode(PATH_SEPARATOR, $var);
            } else {
                $paths = array();
            }

            foreach ($paths as $path) {
                if (file_exists($path . DIRECTORY_SEPARATOR . $binary) &&
                    is_executable($path . DIRECTORY_SEPARATOR . $binary))
                {
                    self::$hasDiff = TRUE;
                    break;
                }
            }
        }

        return self::$hasDiff;
    }
}

}

require_once 'PHPUnit/Framework/ComparisonFailure/Array.php';
require_once 'PHPUnit/Framework/ComparisonFailure/Object.php';
require_once 'PHPUnit/Framework/ComparisonFailure/Scalar.php';
require_once 'PHPUnit/Framework/ComparisonFailure/String.php';
require_once 'PHPUnit/Framework/ComparisonFailure/Type.php';
?>
