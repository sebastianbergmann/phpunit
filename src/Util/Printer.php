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
 * @since      File available since Release 2.0.0
 */

/**
 * Utility class that can print to STDOUT or write to a file.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Util_Printer
{
    /**
     * If true, flush output after every write.
     *
     * @var boolean
     */
    protected $autoFlush = false;

    /**
     * @var    resource
     */
    protected $out;

    /**
     * @var    string
     */
    protected $outTarget;

    /**
     * @var    boolean
     */
    protected $printsHTML = false;

    /**
     * Constructor.
     *
     * @param  mixed                       $out
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct($out = null)
    {
        if ($out !== null) {
            if (is_string($out)) {
                if (strpos($out, 'socket://') === 0) {
                    $out = explode(':', str_replace('socket://', '', $out));

                    if (sizeof($out) != 2) {
                        throw new PHPUnit_Framework_Exception;
                    }

                    $this->out = fsockopen($out[0], $out[1]);
                } else {
                    if (strpos($out, 'php://') === false &&
                        !is_dir(dirname($out))) {
                        mkdir(dirname($out), 0777, true);
                    }

                    $this->out = fopen($out, 'wt');
                }

                $this->outTarget = $out;
            } else {
                $this->out = $out;
            }
        }
    }

    /**
     * Flush buffer, optionally tidy up HTML, and close output if it's not to a php stream
     */
    public function flush()
    {
        if ($this->out && strncmp($this->outTarget, 'php://', 6) !== 0) {
            fclose($this->out);
        }

        if ($this->printsHTML === true &&
            $this->outTarget !== null &&
            strpos($this->outTarget, 'php://') !== 0 &&
            strpos($this->outTarget, 'socket://') !== 0 &&
            extension_loaded('tidy')) {
            file_put_contents(
                $this->outTarget,
                tidy_repair_file(
                    $this->outTarget, array('indent' => true, 'wrap' => 0), 'utf8'
                )
            );
        }
    }

    /**
     * Performs a safe, incremental flush.
     *
     * Do not confuse this function with the flush() function of this class,
     * since the flush() function may close the file being written to, rendering
     * the current object no longer usable.
     *
     * @since  Method available since Release 3.3.0
     */
    public function incrementalFlush()
    {
        if ($this->out) {
            fflush($this->out);
        } else {
            flush();
        }
    }

    /**
     * @param string $buffer
     */
    public function write($buffer)
    {
        if ($this->out) {
            fwrite($this->out, $buffer);

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        } else {
            if (PHP_SAPI != 'cli') {
                $buffer = htmlspecialchars($buffer);
            }

            print $buffer;

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        }
    }

    /**
     * Check auto-flush mode.
     *
     * @return boolean
     * @since  Method available since Release 3.3.0
     */
    public function getAutoFlush()
    {
        return $this->autoFlush;
    }

    /**
     * Set auto-flushing mode.
     *
     * If set, *incremental* flushes will be done after each write. This should
     * not be confused with the different effects of this class' flush() method.
     *
     * @param boolean $autoFlush
     * @since  Method available since Release 3.3.0
     */
    public function setAutoFlush($autoFlush)
    {
        if (is_bool($autoFlush)) {
            $this->autoFlush = $autoFlush;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }
}
