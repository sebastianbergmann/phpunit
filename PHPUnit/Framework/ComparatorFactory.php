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
 * @subpackage Framework
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.6.0
 */

/**
 * Factory for comparators which compare values for equality.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.6.0
 */
class PHPUnit_Framework_ComparatorFactory
{
    /**
     * @var array
     */
    protected $comparators = array();

    /**
     * @var PHPUnit_Framework_ComparatorFactory
     */
    private static $defaultInstance = NULL;

    /**
     * Constructs a new factory.
     */
    public function __construct()
    {
        $this->register(new PHPUnit_Framework_Comparator_Type);
        $this->register(new PHPUnit_Framework_Comparator_Scalar);
        $this->register(new PHPUnit_Framework_Comparator_Numeric);
        $this->register(new PHPUnit_Framework_Comparator_Double);
        $this->register(new PHPUnit_Framework_Comparator_Array);
        $this->register(new PHPUnit_Framework_Comparator_Resource);
        $this->register(new PHPUnit_Framework_Comparator_Object);
        $this->register(new PHPUnit_Framework_Comparator_Exception);
        $this->register(new PHPUnit_Framework_Comparator_SplObjectStorage);
        $this->register(new PHPUnit_Framework_Comparator_DOMDocument);
        $this->register(new PHPUnit_Framework_Comparator_MockObject);
    }

    /**
     * Returns the default instance.
     *
     * @return PHPUnit_Framework_ComparatorFactory
     */
    public static function getDefaultInstance()
    {
        if (self::$defaultInstance === NULL) {
            self::$defaultInstance = new PHPUnit_Framework_ComparatorFactory;
        }

        return self::$defaultInstance;
    }

    /**
     * Returns the correct comparator for comparing two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @return PHPUnit_Framework_Comparator
     * @throws PHPUnit_Framework_Exception
     */
    public function getComparatorFor($expected, $actual)
    {
        foreach ($this->comparators as $comparator) {
            if ($comparator->accepts($expected, $actual)) {
                return $comparator;
            }
        }

        throw new PHPUnit_Framework_Exception(
          sprintf(
            'No comparator is registered for comparing the types "%s" and "%s"',
            gettype($expected), gettype($actual)
          )
        );
    }

    /**
     * Registers a new comparator.
     *
     * This comparator will be returned by getInstance() if its accept() method
     * returns TRUE for the compared values. It has higher priority than the
     * existing comparators, meaning that its accept() method will be tested
     * before those of the other comparators.
     *
     * @param  PHPUnit_Framework_Comparator $comparator The registered comparator
     */
    public function register(PHPUnit_Framework_Comparator $comparator)
    {
        array_unshift($this->comparators, $comparator);
        $comparator->setFactory($this);
    }

    /**
     * Unregisters a comparator.
     *
     * This comparator will no longer be returned by getInstance().
     *
     * @param  PHPUnit_Framework_Comparator $comparator The unregistered comparator
     */
    public function unregister(PHPUnit_Framework_Comparator $comparator)
    {
        foreach ($this->comparators as $key => $_comparator) {
            if ($comparator === $_comparator) {
                unset($this->comparators[$key]);
            }
        }
    }
}
