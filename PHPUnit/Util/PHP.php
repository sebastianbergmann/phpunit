<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

/**
 * Utility methods for PHP sub-processes.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Util_PHP
{
    /**
     * Path to the PHP interpreter that is to be used.
     *
     * @var    string $phpBinary
     */
    protected static $phpBinary = NULL;

    /**
     * Descriptor specification for proc_open().
     *
     * @var    array
     */
    protected static $descriptorSpec = array(
      0 => array('pipe', 'r'),
      1 => array('pipe', 'w'),
      2 => array('pipe', 'w')
    );

    /**
     * Returns the path to a PHP interpreter.
     *
     * PHPUnit_Util_PHP::$phpBinary contains the path to the PHP
     * interpreter.
     *
     * When not set, the following assumptions will be made:
     *
     *   1. When the PHP CLI/CGI binary configured with the PEAR Installer
     *      (php_bin configuration value) is readable, it will be used.
     *
     *   2. When PHPUnit is run using the CLI SAPI and the $_SERVER['_']
     *      variable does not contain the string "PHPUnit", $_SERVER['_']
     *      is assumed to contain the path to the current PHP interpreter
     *      and that will be used.
     *
     *   3. When PHPUnit is run using the CLI SAPI and the $_SERVER['_']
     *      variable contains the string "PHPUnit", the file that $_SERVER['_']
     *      points to is assumed to be the PHPUnit TextUI CLI wrapper script
     *      "phpunit" and the binary set up using #! on that file's first
     *      line of code is assumed to contain the path to the current PHP
     *      interpreter and that will be used.
     *
     *   4. The current PHP interpreter is assumed to be in the $PATH and
     *      to be invokable through "php".
     *
     * @return string
     */
    public static function getPhpBinary()
    {
        if (self::$phpBinary === NULL) {
            if (is_readable('@php_bin@')) {
                self::$phpBinary = '@php_bin@';
            }

            else if (PHP_SAPI == 'cli' && isset($_SERVER['_']) &&
                     strpos($_SERVER['_'], 'phpunit') !== FALSE) {
                $file            = file($_SERVER['_']);
                $tmp             = explode(' ', $file[0]);
                self::$phpBinary = trim($tmp[1]);
            }

            if (!is_readable(self::$phpBinary)) {
                self::$phpBinary = 'php';
            } else {
                self::$phpBinary = escapeshellarg(self::$phpBinary);
            }
        }

        return self::$phpBinary;
    }

    /**
     * Runs a single job (PHP code) using a separate PHP process.
     *
     * @param  string                       $job
     * @param  PHPUnit_Framework_TestCase   $test
     * @param  PHPUnit_Framework_TestResult $result
     * @return array|null
     * @throws PHPUnit_Framework_Exception
     */
    public static function runJob($job, PHPUnit_Framework_Test $test = NULL, PHPUnit_Framework_TestResult $result = NULL)
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            return PHPUnit_Util_PHP_Windows::runJob($job, $test, $result);
        }

        return self::doRun($job, $test, $result);
    }

    /**
     * @param  string                       $job
     * @param  PHPUnit_Framework_TestCase   $test
     * @param  PHPUnit_Framework_TestResult $result
     * @return array|null
     * @since Method available since Release 3.5.12
     */
    protected static function doRun($job, PHPUnit_Framework_Test $test = NULL, PHPUnit_Framework_TestResult $result = NULL)
    {
        $process = proc_open(
          self::getPhpBinary(), self::$descriptorSpec, $pipes
        );

        if (is_resource($process)) {
            if ($result !== NULL) {
                $result->startTest($test);
            }

            fwrite($pipes[0], $job);
            fclose($pipes[0]);

            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            proc_close($process);

            if ($result !== NULL) {
                self::processChildResult($test, $result, $stdout, $stderr);
            } else {
                return array('stdout' => $stdout, 'stderr' => $stderr);
            }
        }
    }

    /**
     * Runs a single job (PHP code) using a separate PHP process.
     *
     * @param PHPUnit_Framework_TestCase   $test
     * @param PHPUnit_Framework_TestResult $result
     * @param string                       $stdout
     * @param string                       $stderr
     * @since Method available since Release 3.5.0
     */
    protected static function processChildResult(PHPUnit_Framework_Test $test, PHPUnit_Framework_TestResult $result, $stdout, $stderr)
    {
        if (!empty($stderr)) {
            $time = 0;
            $result->addError(
              $test,
              new RuntimeException(trim($stderr)), $time
            );
        } else {
            $childResult = @unserialize($stdout);

            if ($childResult !== FALSE) {
                if (!empty($childResult['output'])) {
                    print $childResult['output'];
                }

                $test->setResult($childResult['testResult']);
                $test->addToAssertionCount($childResult['numAssertions']);

                $childResult = $childResult['result'];

                if ($result->getCollectCodeCoverageInformation()) {
                    $codeCoverageInformation = $childResult->getRawCodeCoverageInformation();

                    if (isset($codeCoverageInformation[0]) &&
                         is_array($codeCoverageInformation[0])) {
                        $result->getCodeCoverage()->append(
                          $codeCoverageInformation[0], $test
                        );
                    }
                }

                $time           = $childResult->time();
                $notImplemented = $childResult->notImplemented();
                $skipped        = $childResult->skipped();
                $errors         = $childResult->errors();
                $failures       = $childResult->failures();

                if (!empty($notImplemented)) {
                    $result->addError(
                      $test, $notImplemented[0]->thrownException(), $time
                    );
                }

                else if (!empty($skipped)) {
                    $result->addError(
                      $test, $skipped[0]->thrownException(), $time
                    );
                }

                else if (!empty($errors)) {
                    $result->addError(
                      $test, $errors[0]->thrownException(), $time
                    );
                }

                else if (!empty($failures)) {
                    $result->addFailure(
                      $test, $failures[0]->thrownException(), $time
                    );
                }
            } else {
                $time = 0;

                $result->addError(
                  $test, new RuntimeException(trim($stdout)), $time
                );
            }
        }

        $result->endTest($test, $time);
    }
}
