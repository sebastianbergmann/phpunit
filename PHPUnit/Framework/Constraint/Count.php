<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Framework_Constraint
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2010 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.x
 */

/**
 *
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2010 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5.x
 */

class PHPUnit_Framework_Constraint_Count extends PHPUnit_Framework_Constraint
{

    /**
     * @var integer
     */
    protected $_expectedCount = 0;

    /**
     * @param integer $expected
     * @param boolean $strict
     */
    public function __construct($expected)
    {
        $this->_expectedCount = $expected;
    }

    /**
     * Evaluate
     *
     * @param mixed $other
     * @return boolean
     */
    public function evaluate($other)
    {
        if ($this->_expectedCount === $this->getCountOf($other)) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $other
     * @return boolean
     */
    protected function getCountOf($other)
    {
        if ($other instanceof Countable ||
            is_array($other)) {
            return count($other);
        } else if ($other instanceof Iterator) {
            return iterator_count($other);
        }
    }

    /**
     * @param mixed   $other
     * @param string  $description
     * @param boolean $not
     * @return string
     */
    protected function failureDescription($other, $description, $not)
    {
        return 'Count of ' . $this->getCountOf($other) .
        ' does not match expected count of ' . $this->_expectedCount;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'count matches ';
    }
}