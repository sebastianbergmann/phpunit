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
 * @subpackage Util_TestDox
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.3.0
 */

/**
 * Prettifies class and method names for use in TestDox documentation.
 *
 * @package    PHPUnit
 * @subpackage Util_TestDox
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */
class PHPUnit_Util_TestDox_NamePrettifier
{
    /**
     * @var    string
     */
    protected $prefix = 'Test';

    /**
     * @var    string
     */
    protected $suffix = 'Test';

    /**
     * @var    array
     */
    protected $strings = array();

    /**
     * Prettifies the name of a test class.
     *
     * @param  string $name
     * @return string
     */
    public function prettifyTestClass($name)
    {
        $title = $name;

        if ($this->suffix !== null &&
            $this->suffix == substr($name, -1 * strlen($this->suffix))) {
            $title = substr($title, 0, strripos($title, $this->suffix));
        }

        if ($this->prefix !== null &&
            $this->prefix == substr($name, 0, strlen($this->prefix))) {
            $title = substr($title, strlen($this->prefix));
        }

        if (substr($title, 0, 1) == '\\') {
            $title = substr($title, 1);
        }

        return $title;
    }

    /**
     * Prettifies the name of a test method.
     *
     * @param  string $name
     * @return string
     */
    public function prettifyTestMethod($name)
    {
        $buffer = '';

        if (!is_string($name) || strlen($name) == 0) {
            return $buffer;
        }

        $string = preg_replace('#\d+$#', '', $name, -1, $count);

        if (in_array($string, $this->strings)) {
            $name = $string;
        } elseif ($count == 0) {
            $this->strings[] = $string;
        }

        if (strpos($name, '_') !== false) {
            return str_replace('_', ' ', $name);
        }

        $max = strlen($name);

        if (substr($name, 0, 4) == 'test') {
            $offset = 4;
        } else {
            $offset  = 0;
            $name[0] = strtoupper($name[0]);
        }

        $wasNumeric = false;

        for ($i = $offset; $i < $max; $i++) {
            if ($i > $offset &&
                ord($name[$i]) >= 65 &&
                ord($name[$i]) <= 90) {
                $buffer .= ' ' . strtolower($name[$i]);
            } else {
                $isNumeric = is_numeric($name[$i]);

                if (!$wasNumeric && $isNumeric) {
                    $buffer    .= ' ';
                    $wasNumeric = true;
                }

                if ($wasNumeric && !$isNumeric) {
                    $wasNumeric = false;
                }

                $buffer .= $name[$i];
            }
        }

        return $buffer;
    }

    /**
     * Sets the prefix of test names.
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Sets the suffix of test names.
     *
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }
}
