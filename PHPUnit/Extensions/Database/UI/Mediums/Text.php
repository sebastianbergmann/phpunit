<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

/**
 * A text medium for the database extension tool.
 *
 * This class builds the call context based on command line parameters and
 * prints output to stdout and stderr as appropriate.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Extensions_Database_UI_Mediums_Text implements PHPUnit_Extensions_Database_UI_IMedium
{
    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var string
     */
    protected $command;

    /**
     * @param array $arguments
     */
    public function __construct(Array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Builds the context for the application.
     *
     * @param PHPUnit_Extensions_Database_UI_Context $context
     */
    public function buildContext(PHPUnit_Extensions_Database_UI_Context $context)
    {
        $arguments = $this->arguments;
        $this->command = array_shift($arguments);

        $context->setMode(array_shift($arguments));
        $context->setModeArguments($arguments);
    }

    /**
     * Handles the displaying of exceptions received from the application.
     *
     * @param Exception $e
     */
    public function handleException(Exception $e)
    {
        try {
            throw $e;
        } catch (PHPUnit_Extensions_Database_UI_InvalidModeException $invalidMode) {
            if ($invalidMode->getMode() == '') {
                $this->error('Please Specify a Command!' . PHP_EOL);
            } else {
                $this->error('Command Does Not Exist: ' . $invalidMode->getMode() . PHP_EOL);
            }
            $this->error('Valid Commands:' . PHP_EOL);

            foreach ($invalidMode->getValidModes() as $mode) {
                $this->error('    ' . $mode . PHP_EOL);
            }
        } catch (Exception $e) {
            $this->error('Unknown Error: ' . $e->getMessage() . PHP_EOL);
        }
    }

    /**
     * Prints the message to stdout.
     *
     * @param string $message
     */
    public function output($message)
    {
        echo $message;
    }

    /**
     * Prints the message to stderr
     *
     * @param string $message
     */
    public function error($message)
    {
        fputs(STDERR, $message);
    }
}

