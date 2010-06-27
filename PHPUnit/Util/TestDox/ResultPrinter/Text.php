<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Util_TestDox
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.3.0
 */

/**
 * Prints TestDox documentation in text format.
 *
 * @package    PHPUnit
 * @subpackage Util_TestDox
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */
class PHPUnit_Util_TestDox_ResultPrinter_Text extends PHPUnit_Util_TestDox_ResultPrinter
{
    /**
     * Handler for 'start class' event.
     *
     * @param  string $name
     */
    protected function startClass($name)
    {
        $this->write($this->currentTestClassPrettified . "\n");
    }

    /**
     * Handler for 'on test' event.
     *
     * @param  string  $name
     * @param  boolean $success
     */
    protected function onTest($name, $success = TRUE)
    {
        if ($success) {
            $this->write(' [x] ');
        } else {
            $this->write(' [ ] ');
        }

        $this->write($name . "\n");

        foreach ($this->tests[$name]['errors'] as $error) {
            $this->write("     +-> {$error->getMessage()}\n");
            $trace = NULL;
            $stepNum = 1;
            $lineNum = $error->getLine();
            $file = $error->getFile();
            foreach ($error->getTrace() as $traceStep) {
                $line = str_pad('', 8 + $stepNum);
                $line .= ($stepNum == 1 ? '@' : ' ') . " ";
                if (isset($traceStep['class']))
                {
                    $line .= "{$traceStep['class']}::";
                }
                $line .= "{$traceStep['function']}()";
                if ($lineNum)
                {
                    $line .= ":{$lineNum}";
                }

                $line = str_pad($line, 75, " ", STR_PAD_RIGHT);
                $line .= "{$file} ";
                $this->write("{$line}\n");

                // the trace step's file & line are the CALLER, not the current
                $file = (isset($traceStep['file']) ? $traceStep['file'] : '<unknown>');
                $lineNum = (isset($traceStep['line']) ? $traceStep['line'] : NULL);

                $stepNum++;
            }
        }
    }

    /**
     * Handler for 'end class' event.
     *
     * @param  string $name
     */
    protected function endClass($name)
    {
        $this->write("\n");
    }
}
