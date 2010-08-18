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
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2010 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5?
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'Mockable.php';

/**
 * @category   Testing
 * @package    PHPUnit
 * @author     Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @copyright  2010 Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5?
 */
class Framework_MockSpecificationTest extends PHPUnit_Framework_TestCase
{
    public function testMockSpecificationRequiresClassName()
    {
        $spec = $this->getMockSpecification('Mockable');
        $mock = $spec->getMock();
        $this->assertTrue($mock instanceof Mockable);
    }

    public function testByDefaultMocksAllMethods()
    {
        $spec = $this->getMockSpecification('Mockable');
        $mock = $spec->getMock();
        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }

    public function testMethodsToMockCanBeSpecified()
    {
        $spec = $this->getMockSpecification('Mockable');
        $spec->setMethods(array('mockableMethod'));
        $mock = $spec->getMock();
        $this->assertNull($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testByDefaultDoesNotPassArgumentsToTheConstructor()
    {
        $spec = $this->getMockSpecification('Mockable');
        $mock = $spec->getMock();
        $this->assertEquals(array(null, null), $mock->constructorArgs);
    }

    public function testMockClassNameCanBeSpecified()
    {
        $spec = $this->getMockSpecification('Mockable');
        $spec->setMockClassName('ACustomClassName');
        $mock = $spec->getMock();
        $this->assertTrue($mock instanceof ACustomClassName);
    }

    public function testConstructorArgumentsCanBeSpecified()
    {
        $spec = $this->getMockSpecification('Mockable');
        $spec->setConstructorArgs($expected = array(23, 42));
        $mock = $spec->getMock();
        $this->assertEquals($expected, $mock->constructorArgs);
    }

    public function testOriginalConstructorCanBeDisabled()
    {
        $spec = $this->getMockSpecification('Mockable');
        $spec->disableOriginalConstructor();
        $mock = $spec->getMock();
        $this->assertNull($mock->constructorArgs);
    }

    public function testByDefaultOriginalCloneIsPreserved()
    {
        $spec = $this->getMockSpecification('Mockable');
        $mock = $spec->getMock();
        $cloned = clone $mock;
        $this->assertTrue($cloned->cloned);
    }

    public function testOriginalCloneCanBeDisabled()
    {
        $spec = $this->getMockSpecification('Mockable');
        $spec->disableOriginalClone();
        $mock = $spec->getMock();
        $mock->cloned = false;
        $cloned = clone $mock;
        $this->assertFalse($cloned->cloned);
    }

    public function testCallingAutoloadCanBeDisabled()
    {
        $this->markTestIncomplete();
    }

    public function testProvidesAFluentInterface()
    {
        $spec = $this->getMockSpecification('Mockable')
                     ->setMethods(array('mockableMethod'))
                     ->setConstructorArgs(array())
                     ->setMockClassName('DummyClassName')
                     ->disableOriginalConstructor()
                     ->disableOriginalClone();
        $this->assertTrue($spec instanceof PHPUnit_Framework_MockSpecification);
    }
}
