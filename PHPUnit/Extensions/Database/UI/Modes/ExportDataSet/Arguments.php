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
 * @package    PHPUnit
 * @subpackage Extensions_Database_UI_Modes_ExportDataSet
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

/**
 * Represents arguments received from a medium.
 *
 * @package    PHPUnit
 * @subpackage Extensions_Database_UI_Modes_ExportDataSet
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Extensions_Database_UI_Modes_ExportDataSet_Arguments
{
    /**
     * @var array
     */
    protected $arguments = array();

    /**
     * @param array $arguments
     */
    public function __construct(array $arguments)
    {
        foreach ($arguments as $argument) {
            list($argName, $argValue) = explode('=', $argument, 2);

            $argName = trim($argName, '-');

            if (!isset($this->arguments[$argName])) {
                $this->arguments[$argName] = array();
            }

            $this->arguments[$argName][] = $argValue;
        }
    }

    /**
     * Returns an array of arguments matching the given $argName
     *
     * @param string $argName
     * @return array
     */
    public function getArgumentArray($argName)
    {
        if ($this->argumentIsSet($argName)) {
            return $this->arguments[$argName];
        } else {
            return NULL;
        }
    }

    /**
     * Returns a single argument value.
     *
     * If $argName points to an array the first argument will be returned.
     *
     * @param string $argName
     * @return mixed
     */
    public function getSingleArgument($argName)
    {
        if ($this->argumentIsSet($argName)) {
            return reset($this->arguments[$argName]);
        } else {
            return NULL;
        }
    }

    /**
     * Returns whether an argument is set.
     *
     * @param string $argName
     * @return bool
     */
    public function argumentIsSet($argName)
    {
        return array_key_exists($argName, $this->arguments);
    }

    /**
     * Returns an array containing the names of all arguments provided.
     *
     * @return array
     */
    public function getArgumentNames()
    {
        return array_keys($this->arguments);
    }

    /**
     * Returns an array of database arguments keyed by name.
     *
     * @todo this should be moved.
     * @return array
     */
    public function getDatabases()
    {
        $databases = $this->getArgumentArray('database');

        $retDb = array();
        foreach ($databases as $db) {
            list($name, $arg) = explode(':', $db, 2);
            $retDb[$name] = $arg;
        }

        return $retDb;
    }
}

