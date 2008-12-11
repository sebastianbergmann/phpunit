<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class Framework_ConstraintTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PHPUnit_Framework_Constraint_ArrayHasKey
     */
    public function testConstraintArrayHasKey()
    {
        $constraint = new PHPUnit_Framework_Constraint_ArrayHasKey(0);

        $this->assertFalse($constraint->evaluate(array()));
        $this->assertEquals('has the key <integer:0>', $constraint->toString());

        try {
            $constraint->fail(array(), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that an array has the key <integer:0>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ArrayHasKey
     */
    public function testConstraintArrayHasKey2()
    {
        $constraint = new PHPUnit_Framework_Constraint_ArrayHasKey(0);

        try {
            $constraint->fail(array(), 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that an array has the key <integer:0>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ArrayHasKey
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintArrayNotHasKey()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ArrayHasKey(0)
        );

        $this->assertTrue($constraint->evaluate(array()));
        $this->assertEquals('does not have the key <integer:0>', $constraint->toString());

        try {
            $constraint->fail(array(0), '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that an array does not have the key <integer:0>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ArrayHasKey
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintArrayNotHasKey2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ArrayHasKey(0)
        );

        try {
            $constraint->fail(array(0), 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that an array does not have the key <integer:0>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_FileExists
     */
    public function testConstraintFileExists()
    {
        $constraint = new PHPUnit_Framework_Constraint_FileExists;

        $this->assertFalse($constraint->evaluate('foo'));
        $this->assertEquals('file exists', $constraint->toString());

        try {
            $constraint->fail('foo', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that file "foo" exists.',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_FileExists
     */
    public function testConstraintFileExists2()
    {
        $constraint = new PHPUnit_Framework_Constraint_FileExists;

        try {
            $constraint->fail('foo', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that file \"foo\" exists.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_FileExists
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintFileNotExists()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_FileExists
        );

        $this->assertTrue($constraint->evaluate('foo'));
        $this->assertEquals('file does not exist', $constraint->toString());

        try {
            $constraint->fail('foo', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that file "foo" does not exist.',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_FileExists
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintFileNotExists2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_FileExists
        );

        try {
            $constraint->fail('foo', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that file \"foo\" does not exist.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     */
    public function testConstraintGreaterThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_GreaterThan(1);

        $this->assertFalse($constraint->evaluate(0));
        $this->assertTrue($constraint->evaluate(2));
        $this->assertEquals('is greater than <integer:1>', $constraint->toString());

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:0> is greater than <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     */
    public function testConstraintGreaterThan2()
    {
        $constraint = new PHPUnit_Framework_Constraint_GreaterThan(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:0> is greater than <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintNotGreaterThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_GreaterThan(1)
        );

        $this->assertTrue($constraint->evaluate(1));
        $this->assertEquals('is not greater than <integer:1>', $constraint->toString());

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:1> is not greater than <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_GreaterThan
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintNotGreaterThan2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_GreaterThan(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:1> is not greater than <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsAnything
     */
    public function testConstraintIsAnything()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsAnything;

        $this->assertTrue($constraint->evaluate(NULL));
        $this->assertNull($constraint->fail(NULL, ''));
        $this->assertEquals('is anything', $constraint->toString());
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsAnything
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintNotIsAnything()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsAnything
        );

        $this->assertFalse($constraint->evaluate(NULL));
        $this->assertNull($constraint->fail(NULL, ''));
        $this->assertEquals('is not anything', $constraint->toString());
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     */
    public function testConstraintIsEqual()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsEqual(1);

        $this->assertFalse($constraint->evaluate(0));
        $this->assertTrue($constraint->evaluate(1));
        $this->assertEquals('is equal to <integer:1>', $constraint->toString());

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:0> is equal to <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     */
    public function testConstraintIsEqual2()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsEqual(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:0> is equal to <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintIsNotEqual()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsEqual(1)
        );

        $this->assertTrue($constraint->evaluate(0));
        $this->assertFalse($constraint->evaluate(1));
        $this->assertEquals('is not equal to <integer:1>', $constraint->toString());

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:1> is not equal to <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintIsNotEqual2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsEqual(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:1> is not equal to <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsIdentical
     */
    public function testConstraintIsIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new PHPUnit_Framework_Constraint_IsIdentical($a);

        $this->assertFalse($constraint->evaluate($b));
        $this->assertTrue($constraint->evaluate($a));
        $this->assertEquals('is identical to an object of class "stdClass"', $constraint->toString());

        try {
            $constraint->fail($b, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nstdClass Object\n(\n)\n is identical to an object of class \"stdClass\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsIdentical
     */
    public function testConstraintIsIdentical2()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new PHPUnit_Framework_Constraint_IsIdentical($a);

        try {
            $constraint->fail($b, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that \nstdClass Object\n(\n)\n is identical to an object of class \"stdClass\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsIdentical
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintIsNotIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsIdentical($a)
        );

        $this->assertTrue($constraint->evaluate($b));
        $this->assertFalse($constraint->evaluate($a));
        $this->assertEquals("is not identical to an object of class \"stdClass\"", $constraint->toString());

        try {
            $constraint->fail($a, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nstdClass Object\n(\n)\n is not identical to an object of class \"stdClass\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsIdentical
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintIsNotIdentical2()
    {
        $a = new stdClass;

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsIdentical($a)
        );

        try {
            $constraint->fail($a, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that \nstdClass Object\n(\n)\n is not identical to an object of class \"stdClass\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsInstanceOf
     */
    public function testConstraintIsInstanceOf()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsInstanceOf('Exception');

        $this->assertFalse($constraint->evaluate(new stdClass));
        $this->assertTrue($constraint->evaluate(new Exception));
        $this->assertEquals('is instance of class "Exception"', $constraint->toString());

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <stdClass> is an instance of class "Exception".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsInstanceOf
     */
    public function testConstraintIsInstanceOf2()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsInstanceOf('Exception');

        try {
            $constraint->fail(new stdClass, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <stdClass> is an instance of class \"Exception\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsInstanceOf
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintIsNotInstanceOf()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsInstanceOf('stdClass')
        );

        $this->assertFalse($constraint->evaluate(new stdClass));
        $this->assertTrue($constraint->evaluate(new Exception));
        $this->assertEquals('is not instance of class "stdClass"', $constraint->toString());

        try {
            $constraint->fail(new stdClass, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <stdClass> is not an instance of class "stdClass".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsInstanceOf
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintIsNotInstanceOf2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsInstanceOf('stdClass')
        );

        try {
            $constraint->fail(new stdClass, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <stdClass> is not an instance of class \"stdClass\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsType
     */
    public function testConstraintIsType()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsType('string');

        $this->assertFalse($constraint->evaluate(0));
        $this->assertTrue($constraint->evaluate(''));
        $this->assertEquals('is of type "string"', $constraint->toString());

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nstdClass Object\n(\n)\n is of type \"string\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsType
     */
    public function testConstraintIsType2()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsType('string');

        try {
            $constraint->fail(new stdClass, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that \nstdClass Object\n(\n)\n is of type \"string\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsType
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintIsNotType()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsType('string')
        );

        $this->assertTrue($constraint->evaluate(0));
        $this->assertFalse($constraint->evaluate(''));
        $this->assertEquals('is not of type "string"', $constraint->toString());

        try {
            $constraint->fail('', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <string:> is not of type "string".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsType
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintIsNotType2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsType('string')
        );

        try {
            $constraint->fail('', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:> is not of type \"string\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_LessThan
     */
    public function testConstraintLessThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_LessThan(1);

        $this->assertTrue($constraint->evaluate(0));
        $this->assertFalse($constraint->evaluate(2));
        $this->assertEquals('is less than <integer:1>', $constraint->toString());

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:0> is less than <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_LessThan
     */
    public function testConstraintLessThan2()
    {
        $constraint = new PHPUnit_Framework_Constraint_LessThan(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:0> is less than <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintNotLessThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_LessThan(1)
        );

        $this->assertTrue($constraint->evaluate(1));
        $this->assertEquals('is not less than <integer:1>', $constraint->toString());

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:1> is not less than <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_LessThan
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintNotLessThan2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_LessThan(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:1> is not less than <integer:1>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ObjectHasAttribute
     */
    public function testConstraintObjectHasAttribute()
    {
        $constraint = new PHPUnit_Framework_Constraint_ObjectHasAttribute('foo');

        $this->assertFalse($constraint->evaluate(new stdClass));
        $this->assertEquals('has attribute "foo"', $constraint->toString());

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that object of class "stdClass" has attribute "foo".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ObjectHasAttribute
     */
    public function testConstraintObjectHasAttribute2()
    {
        $constraint = new PHPUnit_Framework_Constraint_ObjectHasAttribute('foo');

        try {
            $constraint->fail(new stdClass, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that object of class \"stdClass\" has attribute \"foo\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ObjectHasAttribute
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintObjectNotHasAttribute()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ObjectHasAttribute('foo')
        );

        $this->assertTrue($constraint->evaluate(new stdClass));
        $this->assertEquals('does not have attribute "foo"', $constraint->toString());

        $o = new stdClass;
        $o->foo = 'bar';

        try {
            $constraint->fail($o, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that object of class "stdClass" does not have attribute "foo".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_ObjectHasAttribute
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintObjectNotHasAttribute2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ObjectHasAttribute('foo')
        );

        $o = new stdClass;
        $o->foo = 'bar';

        try {
            $constraint->fail($o, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that object of class \"stdClass\" does not have attribute \"foo\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_PCREMatch
     */
    public function testConstraintPCREMatch()
    {
        $constraint = new PHPUnit_Framework_Constraint_PCREMatch('/foo/');

        $this->assertFalse($constraint->evaluate('barbazbar'));
        $this->assertTrue($constraint->evaluate('barfoobar'));
        $this->assertEquals('matches PCRE pattern "/foo/"', $constraint->toString());

        try {
            $constraint->fail('barbazbar', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <string:barbazbar> matches PCRE pattern "/foo/".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_PCREMatch
     */
    public function testConstraintPCREMatch2()
    {
        $constraint = new PHPUnit_Framework_Constraint_PCREMatch('/foo/');

        try {
            $constraint->fail('barbazbar', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:barbazbar> matches PCRE pattern \"/foo/\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_PCREMatch
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintPCRENotMatch()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_PCREMatch('/foo/')
        );

        $this->assertTrue($constraint->evaluate('barbazbar'));
        $this->assertFalse($constraint->evaluate('barfoobar'));
        $this->assertEquals('does not match PCRE pattern "/foo/"', $constraint->toString());

        try {
            $constraint->fail('barfoobar', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <string:barfoobar> does not match PCRE pattern "/foo/".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_PCREMatch
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintPCRENotMatch2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_PCREMatch('/foo/')
        );

        try {
            $constraint->fail('barfoobar', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:barfoobar> does not match PCRE pattern \"/foo/\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringContains
     */
    public function testConstraintStringContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_StringContains('foo');

        $this->assertFalse($constraint->evaluate('barbazbar'));
        $this->assertTrue($constraint->evaluate('barfoobar'));
        $this->assertEquals('contains "foo"', $constraint->toString());

        try {
            $constraint->fail('barbazbar', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <string:barbazbar> contains "foo".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringContains
     */
    public function testConstraintStringContains2()
    {
        $constraint = new PHPUnit_Framework_Constraint_StringContains('foo');

        try {
            $constraint->fail('barbazbar', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:barbazbar> contains \"foo\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringContains
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintStringNotContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_StringContains('foo')
        );

        $this->assertTrue($constraint->evaluate('barbazbar'));
        $this->assertFalse($constraint->evaluate('barfoobar'));
        $this->assertEquals('does not contain "foo"', $constraint->toString());

        try {
            $constraint->fail('barfoobar', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <string:barfoobar> does not contain "foo".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringContains
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintStringNotContains2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_StringContains('foo')
        );

        try {
            $constraint->fail('barfoobar', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:barfoobar> does not contain \"foo\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     */
    public function testConstraintTraversableContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains('foo');

        $this->assertFalse($constraint->evaluate(array('bar')));
        $this->assertTrue($constraint->evaluate(array('foo')));
        $this->assertEquals('contains <string:foo>', $constraint->toString());

        try {
            $constraint->fail(array('bar'), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that an array contains <string:foo>.',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     */
    public function testConstraintTraversableContains2()
    {
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains('foo');

        try {
            $constraint->fail(array('bar'), 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that an array contains <string:foo>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintTraversableNotContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_TraversableContains('foo')
        );

        $this->assertTrue($constraint->evaluate(array('bar')));
        $this->assertFalse($constraint->evaluate(array('foo')));
        $this->assertEquals('does not contain <string:foo>', $constraint->toString());

        try {
            $constraint->fail(array('foo'), '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that an array does not contain <string:foo>.',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     * @covers PHPUnit_Framework_Constraint_Not
     */
    public function testConstraintTraversableNotContains2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_TraversableContains('foo')
        );

        try {
            $constraint->fail(array('foo'), 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that an array does not contain <string:foo>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }
}
?>
