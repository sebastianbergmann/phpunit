<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2012, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Adam Harvey <aharvey@php.net>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.9.0
 */

/**
 * A context containing previously rendered arrays and objects when recursively
 * exporting a value.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Adam Harvey <aharvey@php.net>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.9.0
 */
class PHPUnit_Util_Type_ExportContext {
    /**
     * Previously seen arrays.
     *
     * @var array[] $arrays
     */
    protected $arrays;

    /**
     * Previously seen objects.
     *
     * @var SplObjectStorage $objects
     */
    protected $objects;

    /** Initialises the context. */
    public function __construct()
    {
        $this->arrays = [];
        $this->objects = new SplObjectStorage;
    }

    /**
     * Adds a value to the export context.
     *
     * @param mixed $value The value to add.
     * @return mixed The ID of the stored value, either as a string or integer.
     * @throws PHPUnit_Framework_Exception Thrown if $value is not an array or
     *                                     object.
     */
    public function add(&$value)
    {
        if (is_array($value)) {
            return $this->addArray($value);
        } elseif (is_object($value)) {
            return $this->addObject($value);
        }

        throw new PHPUnit_Framework_Exception('Only arrays and objects are supported');
    }

    /**
     * Checks if the given value exists within the context.
     *
     * @param mixed $value The value to check.
     * @return mixed The string or integer ID of the stored value if it has
     *               already been seen, or boolean false if the value is not
     *               stored.
     * @throws PHPUnit_Framework_Exception Thrown if $value is not an array or
     *                                     object.
     */
    public function contains(&$value)
    {
        if (is_array($value)) {
            return $this->containsArray($value);
        } elseif (is_object($value)) {
            return $this->containsObject($value);
        }

        throw new PHPUnit_Framework_Exception('Only arrays and objects are supported');
    }

    /**
     * Stores an array within the context.
     *
     * @param array $value The value to store.
     * @return integer The internal ID of the array.
     */
    protected function addArray(array &$value)
    {
        if (($key = $this->containsArray($value)) !== false) {
            return $key;
        }

        $this->arrays[] = &$value;
        return count($this->arrays) - 1;
    }

    /**
     * Stores an object within the context.
     *
     * @param object $value
     * @return string The ID of the object.
     */
    protected function addObject($value)
    {
        if (!$this->objects->contains($value)) {
            $this->objects->attach($value);
        }

        return spl_object_hash($value);
    }

    /**
     * Checks if the given array exists within the context.
     *
     * @param array $value The array to check.
     * @return mixed The integer ID of the array if it exists, or boolean false
     *               otherwise.
     */
    protected function containsArray(array &$value)
    {
        $keys = array_keys($this->arrays, $value, true);
        $gen = '_PHPUnit_Test_Key_'.hash('sha512', microtime(true));
        foreach ($keys as $key) {
            $this->arrays[$key][$gen] = $gen;
            if (isset($value[$gen]) && $value[$gen] === $gen) {
                unset($this->arrays[$key][$gen]);
                return $key;
            }
            unset($this->arrays[$key][$gen]);
        }

        return false;
    }

    /**
     * Checks if the given object exists within the context.
     *
     * @param object $value The object to check.
     * @return mixed The string ID of the object if it exists, or boolean false
     *               otherwise.
     */
    protected function containsObject($value)
    {
        if ($this->objects->contains($value)) {
            return spl_object_hash($value);
        }

        return false;
    }
}
