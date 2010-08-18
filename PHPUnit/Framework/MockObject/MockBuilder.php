<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 */

/**
 * Implementation of the Builder pattern for Mock objects.
 *
 * @package    PHPUnit
 * @subpackage Framework_MockObject
 * @author     Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @copyright  2010 Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 * @since      File available since Release 3.5
 */
class PHPUnit_Framework_MockObject_MockBuilder
{
    protected $testCase;
    protected $className;
    protected $methods = array();
    protected $mockClassName = '';
    protected $constructorArgs = array();
    protected $originalConstructor = true;
    protected $originalClone = true;
    protected $autoload = true;

    /**
     * @param PHPUnit_Framework_TestCase
     * @param string
     */
    public function __construct(PHPUnit_Framework_TestCase $testCase,
                                $className)
    {
        $this->testCase = $testCase;
        $this->className = $className;
    }

    /**
     * @return object
     */
    public function getMock()
    {
        return $this->testCase->getMock($this->className,
                                        $this->methods,
                                        $this->constructorArgs,
                                        $this->mockClassName,
                                        $this->originalConstructor,
                                        $this->originalClone);
    }

    /**
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function setConstructorArgs(array $args)
    {
        $this->constructorArgs = $args;
        return $this;
    }

    /**
     * @param string
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function setMockClassName($name)
    {
        $this->mockClassName = $name;
        return $this;
    }

    /**
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function disableOriginalConstructor()
    {
        $this->originalConstructor = false;
        return $this;
    } 

    /**
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function disableOriginalClone()
    {
        $this->originalClone = false;
        return $this;
    } 

    /**
     * @return PHPUnit_Framework_MockSpecification  provides a fluent interface
     */
    public function disableAutoload()
    {
        $this->autoload = false;
        return $this;
    }
}
