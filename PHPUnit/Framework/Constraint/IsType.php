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
 * @subpackage Framework_Constraint
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * Constraint that asserts that the value it is evaluated for is of a
 * specified type.
 *
 * The expected value is passed in the constructor.
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_Constraint_IsType extends PHPUnit_Framework_Constraint
{
    const TYPE_ARRAY    = 'array';
    const TYPE_BOOL     = 'bool';
    const TYPE_FLOAT    = 'float';
    const TYPE_INT      = 'int';
    const TYPE_NULL     = 'null';
    const TYPE_NUMERIC  = 'numeric';
    const TYPE_OBJECT   = 'object';
    const TYPE_RESOURCE = 'resource';
    const TYPE_STRING   = 'string';
    const TYPE_SCALAR   = 'scalar';
    const TYPE_CALLABLE = 'callable';

    /**
     * @var array
     */
    protected $types = array(
      'array' => TRUE,
      'boolean' => TRUE,
      'bool' => TRUE,
      'float' => TRUE,
      'integer' => TRUE,
      'int' => TRUE,
      'null' => TRUE,
      'numeric' => TRUE,
      'object' => TRUE,
      'resource' => TRUE,
      'string' => TRUE,
      'scalar' => TRUE,
      'callable' => TRUE
    );

    /**
     * @var string
     */
    protected $type;

    /**
     * @param  string $type
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct($type)
    {
        if (!isset($this->types[$type])) {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'Type specified for PHPUnit_Framework_Constraint_IsType <%s> ' .
                'is not a valid type.',
                $type
              )
            );
        }

        $this->type = $type;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    protected function matches($other)
    {
        switch ($this->type) {
            case 'numeric': {
                return is_numeric($other);
            }

            case 'integer':
            case 'int': {
                return is_integer($other);
            }

            case 'float': {
                return is_float($other);
            }

            case 'string': {
                return is_string($other);
            }

            case 'boolean':
            case 'bool': {
                return is_bool($other);
            }

            case 'null': {
                return is_null($other);
            }

            case 'array': {
                return is_array($other);
            }

            case 'object': {
                return is_object($other);
            }

            case 'resource': {
                return is_resource($other);
            }

            case 'scalar': {
                return is_scalar($other);
            }

            case 'callable': {
                return is_callable($other);
            }
        }
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf(
          'is of type "%s"',

          $this->type
        );
    }
}
