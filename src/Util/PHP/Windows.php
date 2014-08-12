<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.12
 */

use SebastianBergmann\Environment\Runtime;

/**
 * Windows utility for PHP sub-processes.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5.12
 */
class PHPUnit_Util_PHP_Windows extends PHPUnit_Util_PHP_Default
{
    /**
     * @var string
     */
    private $tempFile;

    /**
     * {@inheritdoc}
     *
     * Reading from STDOUT or STDERR hangs forever on Windows if the output is
     * too large.
     *
     * @see https://bugs.php.net/bug.php?id=51800
     */
    public function runJob($job, array $settings = array())
    {
        $runtime = new Runtime;

        if (false === $stdout_handle = tmpfile()) {
            throw new PHPUnit_Framework_Exception(
                'A temporary file could not be created; verify that your TEMP environment variable is writable'
            );
        }

        $process = proc_open(
            $runtime->getBinary() . $this->settingsToParameters($settings),
            array(
            0 => array('pipe', 'r'),
            1 => $stdout_handle,
            2 => array('pipe', 'w')
            ),
            $pipes
        );

        if (!is_resource($process)) {
            throw new PHPUnit_Framework_Exception(
                'Unable to spawn worker process'
            );
        }

        $this->process($pipes[0], $job);
        fclose($pipes[0]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        rewind($stdout_handle);
        $stdout = stream_get_contents($stdout_handle);
        fclose($stdout_handle);

        $this->cleanup();

        return array('stdout' => $stdout, 'stderr' => $stderr);
    }

    /**
     * @param  resource                    $pipe
     * @param  string                      $job
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.5.12
     */
    protected function process($pipe, $job)
    {
        if (!($this->tempFile = tempnam(sys_get_temp_dir(), 'PHPUnit')) ||
            file_put_contents($this->tempFile, $job) === false) {
            throw new PHPUnit_Framework_Exception(
                'Unable to write temporary file'
            );
        }

        fwrite(
            $pipe,
            "<?php require_once " . var_export($this->tempFile, true) .  "; ?>"
        );
    }

    /**
     * @since Method available since Release 3.5.12
     */
    protected function cleanup()
    {
        unlink($this->tempFile);
    }
}
