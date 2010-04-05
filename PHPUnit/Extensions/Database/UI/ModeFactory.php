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
 * @subpackage Extensions_Database_UI
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

/**
 * The default factory for db extension modes.
 *
 * @package    PHPUnit
 * @subpackage Extensions_Database_UI
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de//**
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Extensions_Database_UI_ModeFactory implements PHPUnit_Extensions_Database_UI_IModeFactory
{
    /**
     * Generates a new mode based on a given name.
     *
     * @param string $mode
     * @return PHPUnit_Extensions_Database_UI_IMode
     */
    public function getMode($mode)
    {
        if ($mode == '') {
            throw new PHPUnit_Extensions_Database_UI_InvalidModeException($mode, 'A mode was not provided.', $this);
        }

        $modeMap = $this->getModeMap();
        if (isset($modeMap[$mode])) {
            $modeClass = $this->getModeClass($mode, $modeMap[$mode]);

            return new $modeClass();
        } else {
            throw new PHPUnit_Extensions_Database_UI_InvalidModeException($mode, 'The mode does not exist. Attempting to load mode ' . $mode, $this);
        }
    }

    /**
     * Returns the names of valid modes this factory can create.
     *
     * @return array
     */
    public function getModeList()
    {
        return array_keys($this->getModeMap());
    }

    /**
     * Returns a map of modes to class name parts
     *
     * @return array
     */
    protected function getModeMap()
    {
        return array('export-dataset' => 'ExportDataSet');
    }

    /**
     * Given a $mode label and a $mode_name class part attempts to return the
     * class name necessary to instantiate the mode.
     *
     * @param string $mode
     * @param string $mode_name
     * @return string
     */
    protected function getModeClass($mode, $mode_name)
    {
        $modeClass = 'PHPUnit_Extensions_Database_UI_Modes_' . $mode_name;
        $modeFile = dirname(__FILE__) . '/Modes/' . $mode_name . '.php';

        if (class_exists($modeClass)) {
            return $modeClass;
        }

        if (!is_readable($modeFile)) {
            throw new PHPUnit_Extensions_Database_UI_InvalidModeException($mode, 'The mode\'s file could not be loaded. Trying file ' . $modeFile, $this);
        }

        require_once ($modeFile);

        if (!class_exists($modeClass)) {
            throw new PHPUnit_Extensions_Database_UI_InvalidModeException($mode, 'The mode class was not found in the file. Expecting class name ' . $modeClass, $this);
        }

        return $modeClass;
    }
}

