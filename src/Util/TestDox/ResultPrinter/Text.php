<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Prints TestDox documentation in text format.
 *
 * @since Class available since Release 2.1.0
 */
class PHPUnit_Util_TestDox_ResultPrinter_Text extends PHPUnit_Util_TestDox_ResultPrinter
{
    /**
     * Handler for 'start class' event.
     *
     * @param string $name
     */
    protected function startClass($name)
    {
        $this->write($this->currentTestClassPrettified . "\n");
    }

    /**
     * Handler for 'on test' event.
     *
     * @param string $name
     * @param bool   $success
     */
    protected function onTest($name, $success = true)
    {
        $this->write(" [{$this->tests[$name]['status']}] {$name}\n");

        if ($this->verbose)
        {
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
    }

    /**
     * Handler for 'end class' event.
     *
     * @param string $name
     */
    protected function endClass($name)
    {
        $this->write("\n");
    }
}
