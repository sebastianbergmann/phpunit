<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Template
{
    /**
     * @var    string
     * @access private
     */
    private static $date = '';

    /**
     * @var    string
     * @access private
     */
    private $template = '';

    /**
     * @var    array
     * @access private
     */
    private $keys = array();

    /**
     * @var    array
     * @access private
     */
    private $values = array();

    /**
     * Constructor.
     *
     * @param  string $file
     * @throws InvalidArgumentException
     * @access public
     */
    public function __construct($file = '') {
        $this->setFile($file);
    }

    /**
     * Sets the template file.
     *
     * @param  string $file
     * @throws InvalidArgumentException
     * @access public
     */
    public function setFile($file) {
        if ($file != '' && file_exists($file)) {
            $this->template = file_get_contents($file);
        } else {
            throw new InvalidArgumentException(
              'Template file could not be loaded.'
            );
        }
    }

    /**
     * Sets one or more template variables.
     *
     * @param  mixed $keys
     * @param  mixed $values
     * @access public
     */
    public function setVar($keys, $values) {
        if (is_array($keys) && is_array($values) && count($keys) == count($values)) {
            foreach ($keys as $key) {
                $this->keys[] = '{' . $key . '}';
            }

            $this->values = array_merge($this->values, $values);
        } else {
            $this->keys[]   = '{' . $keys . '}';
            $this->values[] = $values;
        }
    }

    /**
     * Renders the template and returns the result.
     *
     * @return string
     * @access public
     */
    public function render() {
        return str_replace($this->keys, $this->values, $this->template);
    }

    /**
     * Renders the template and writes the result to a file.
     *
     * @param string $target
     * @access public
     */
    public function renderTo($target) {
        if ($fp = @fopen($target, 'wt')) {
            fwrite($fp, $this->render());
            fclose($fp);
        } else {
            throw new RuntimeException('Could not write to ' . $target . '.');
        }
    }

    /**
     * Returns the cached result of date('D M j G:i:s T Y').
     *
     * @return string
     * @access public
     * @since  Method available since Release 3.0.1
     */
    public static function getDate() {
        if (self::$date == '') {
            self::$date = date('D M j G:i:s T Y');
        }

        return self::$date;
    }
}
?>
