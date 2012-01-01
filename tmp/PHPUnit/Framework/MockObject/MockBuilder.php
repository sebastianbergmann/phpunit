<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2010-2012, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit_MockObject
 * @author     Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2010-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 * @since      File available since Release 1.0.0
 */

/**
 * Implementation of the Builder pattern for Mock objects.
 *
 * @package    PHPUnit_MockObject
 * @author     Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2010-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 * @since      File available since Release 1.0.0
 */
class PHPUnit_Framework_MockObject_MockBuilder
{
    /**
     * @var PHPUnit_Framework_TestCase
     */
    protected $testCase;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    protected $methods = array();

    /**
     * @var string
     */
    protected $mockClassName = '';

    /**
     * @var array
     */
    protected $constructorArgs = array();

    /**
     * @var boolean
     */
    protected $originalConstructor = TRUE;

    /**
     * @var boolean
     */
    protected $originalClone = TRUE;

    /**
     * @var boolean
     */
    protected $autoload = TRUE;

    /**
     * @param PHPUnit_Framework_TestCase
     * @param string
     */
    public function __construct(PHPUnit_Framework_TestCase $testCase, $className)
    {
        $this->testCase  = $testCase;
        $this->className = $className;
    }

    /**
     * Creates a mock object using a fluent interface.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getMock()
    {
        return $this->testCase->getMock(
          $this->className,
          $this->methods,
          $this->constructorArgs,
          $this->mockClassName,
          $this->originalConstructor,
          $this->originalClone,
          $this->autoload
        );
    }

    /**
     * Creates a mock object for an abstract class using a fluent interface.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockForAbstractClass()
    {
        return $this->testCase->getMockForAbstractClass(
          $this->className,
          $this->constructorArgs,
          $this->mockClassName,
          $this->originalConstructor,
          $this->originalClone,
          $this->autoload
        );
    }

    /**
     * Specifies the subset of methods to mock. Default is to mock all of them.
     *
     * @param  array|null $methods
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Specifies the arguments for the constructor.
     *
     * @param  array $args
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function setConstructorArgs(array $args)
    {
        $this->constructorArgs = $args;

        return $this;
    }

    /**
     * Specifies the name for the mock class.
     *
     * @param string $name
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function setMockClassName($name)
    {
        $this->mockClassName = $name;

        return $this;
    }

    /**
     * Suppresses the invocation of the original constructor.
     *
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function disableOriginalConstructor()
    {
        $this->originalConstructor = FALSE;

        return $this;
    }

    /**
     * Suppresses the invocation of the original clone constructor.
     *
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function disableOriginalClone()
    {
        $this->originalClone = FALSE;

        return $this;
    }

    /**
     * Suppresses the use of class autoloading while creating the mock object.
     *
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function disableAutoload()
    {
        $this->autoload = FALSE;

        return $this;
    }
}
