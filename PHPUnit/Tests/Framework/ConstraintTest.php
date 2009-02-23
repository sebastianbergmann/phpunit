<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
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
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class Framework_ConstraintTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PHPUnit_Framework_Constraint_ArrayHasKey
     * @covers PHPUnit_Framework_Assert::arrayHasKey
     */
    public function testConstraintArrayHasKey()
    {
        $constraint = PHPUnit_Framework_Assert::arrayHasKey(0);

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
     * @covers PHPUnit_Framework_Assert::arrayHasKey
     */
    public function testConstraintArrayHasKey2()
    {
        $constraint = PHPUnit_Framework_Assert::arrayHasKey(0);

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
     * @covers PHPUnit_Framework_Assert::arrayHasKey
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintArrayNotHasKey()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::arrayHasKey(0)
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
     * @covers PHPUnit_Framework_Assert::arrayHasKey
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintArrayNotHasKey2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::arrayHasKey(0)
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
     * @covers PHPUnit_Framework_Assert::fileExists
     */
    public function testConstraintFileExists()
    {
        $constraint = PHPUnit_Framework_Assert::fileExists();

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
     * @covers PHPUnit_Framework_Assert::fileExists
     */
    public function testConstraintFileExists2()
    {
        $constraint = PHPUnit_Framework_Assert::fileExists();

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
     * @covers PHPUnit_Framework_Assert::logicalNot
     * @covers PHPUnit_Framework_Assert::fileExists
     */
    public function testConstraintFileNotExists()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::fileExists()
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
     * @covers PHPUnit_Framework_Assert::logicalNot
     * @covers PHPUnit_Framework_Assert::fileExists
     */
    public function testConstraintFileNotExists2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::fileExists()
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
     * @covers PHPUnit_Framework_Assert::greaterThan
     */
    public function testConstraintGreaterThan()
    {
        $constraint = PHPUnit_Framework_Assert::greaterThan(1);

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
     * @covers PHPUnit_Framework_Assert::greaterThan
     */
    public function testConstraintGreaterThan2()
    {
        $constraint = PHPUnit_Framework_Assert::greaterThan(1);

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
     * @covers PHPUnit_Framework_Assert::greaterThan
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotGreaterThan()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::greaterThan(1)
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
     * @covers PHPUnit_Framework_Assert::greaterThan
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotGreaterThan2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::greaterThan(1)
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
     * @covers PHPUnit_Framework_Assert::anything
     */
    public function testConstraintIsAnything()
    {
        $constraint = PHPUnit_Framework_Assert::anything();

        $this->assertTrue($constraint->evaluate(NULL));
        $this->assertNull($constraint->fail(NULL, ''));
        $this->assertEquals('is anything', $constraint->toString());
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsAnything
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::anything
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotIsAnything()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::anything()
        );

        $this->assertFalse($constraint->evaluate(NULL));
        $this->assertNull($constraint->fail(NULL, ''));
        $this->assertEquals('is not anything', $constraint->toString());
    }

    /**
     * @covers PHPUnit_Framework_Constraint_IsEqual
     * @covers PHPUnit_Framework_Assert::equalTo
     */
    public function testConstraintIsEqual()
    {
        $constraint = PHPUnit_Framework_Assert::equalTo(1);

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
     * @covers PHPUnit_Framework_Assert::equalTo
     */
    public function testConstraintIsEqual2()
    {
        $constraint = PHPUnit_Framework_Assert::equalTo(1);

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
     * @covers PHPUnit_Framework_Assert::equalTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotEqual()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::equalTo(1)
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
     * @covers PHPUnit_Framework_Assert::equalTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotEqual2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::equalTo(1)
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
     * @covers PHPUnit_Framework_Assert::identicalTo
     */
    public function testConstraintIsIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = PHPUnit_Framework_Assert::identicalTo($a);

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
     * @covers PHPUnit_Framework_Assert::identicalTo
     */
    public function testConstraintIsIdentical2()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = PHPUnit_Framework_Assert::identicalTo($a);

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
     * @covers PHPUnit_Framework_Assert::identicalTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::identicalTo($a)
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
     * @covers PHPUnit_Framework_Assert::identicalTo
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotIdentical2()
    {
        $a = new stdClass;

        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::identicalTo($a)
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
     * @covers PHPUnit_Framework_Assert::isInstanceOf
     */
    public function testConstraintIsInstanceOf()
    {
        $constraint = PHPUnit_Framework_Assert::isInstanceOf('Exception');

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
     * @covers PHPUnit_Framework_Assert::isInstanceOf
     */
    public function testConstraintIsInstanceOf2()
    {
        $constraint = PHPUnit_Framework_Assert::isInstanceOf('Exception');

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
     * @covers PHPUnit_Framework_Assert::isInstanceOf
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotInstanceOf()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::isInstanceOf('stdClass')
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
     * @covers PHPUnit_Framework_Assert::isInstanceOf
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotInstanceOf2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::isInstanceOf('stdClass')
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
     * @covers PHPUnit_Framework_Assert::isType
     */
    public function testConstraintIsType()
    {
        $constraint = PHPUnit_Framework_Assert::isType('string');

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
     * @covers PHPUnit_Framework_Assert::isType
     */
    public function testConstraintIsType2()
    {
        $constraint = PHPUnit_Framework_Assert::isType('string');

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
     * @covers PHPUnit_Framework_Assert::isType
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotType()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::isType('string')
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
     * @covers PHPUnit_Framework_Assert::isType
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintIsNotType2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::isType('string')
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
     * @covers PHPUnit_Framework_Assert::lessThan
     */
    public function testConstraintLessThan()
    {
        $constraint = PHPUnit_Framework_Assert::lessThan(1);

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
     * @covers PHPUnit_Framework_Assert::lessThan
     */
    public function testConstraintLessThan2()
    {
        $constraint = PHPUnit_Framework_Assert::lessThan(1);

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
     * @covers PHPUnit_Framework_Assert::lessThan
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotLessThan()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::lessThan(1)
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
     * @covers PHPUnit_Framework_Assert::lessThan
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintNotLessThan2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::lessThan(1)
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
     * @covers PHPUnit_Framework_Assert::objectHasAttribute
     */
    public function testConstraintObjectHasAttribute()
    {
        $constraint = PHPUnit_Framework_Assert::objectHasAttribute('foo');

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
     * @covers PHPUnit_Framework_Assert::objectHasAttribute
     */
    public function testConstraintObjectHasAttribute2()
    {
        $constraint = PHPUnit_Framework_Assert::objectHasAttribute('foo');

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
     * @covers PHPUnit_Framework_Assert::objectHasAttribute
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintObjectNotHasAttribute()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::objectHasAttribute('foo')
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
     * @covers PHPUnit_Framework_Assert::objectHasAttribute
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintObjectNotHasAttribute2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::objectHasAttribute('foo')
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
     * @covers PHPUnit_Framework_Assert::matchesRegularExpression
     */
    public function testConstraintPCREMatch()
    {
        $constraint = PHPUnit_Framework_Assert::matchesRegularExpression('/foo/');

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
     * @covers PHPUnit_Framework_Assert::matchesRegularExpression
     */
    public function testConstraintPCREMatch2()
    {
        $constraint = PHPUnit_Framework_Assert::matchesRegularExpression('/foo/');

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
     * @covers PHPUnit_Framework_Assert::matchesRegularExpression
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintPCRENotMatch()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::matchesRegularExpression('/foo/')
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
     * @covers PHPUnit_Framework_Assert::matchesRegularExpression
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintPCRENotMatch2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::matchesRegularExpression('/foo/')
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
     * @covers PHPUnit_Framework_Constraint_StringStartsWith
     * @covers PHPUnit_Framework_Assert::stringStartsWith
     */
    public function testConstraintStringStartsWith()
    {
        $constraint = PHPUnit_Framework_Assert::stringStartsWith('prefix');

        $this->assertFalse($constraint->evaluate('foo'));
        $this->assertTrue($constraint->evaluate('prefixfoo'));
        $this->assertEquals('starts with "prefix"', $constraint->toString());

        try {
            $constraint->fail('foo', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <string:foo> starts with "prefix".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringStartsWith
     * @covers PHPUnit_Framework_Assert::stringStartsWith
     */
    public function testConstraintStringStartsWith2()
    {
        $constraint = PHPUnit_Framework_Assert::stringStartsWith('prefix');

        try {
            $constraint->fail('foo', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:foo> starts with \"prefix\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringStartsWith
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::stringStartsWith
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintStringStartsNotWith()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringStartsWith('prefix')
        );

        $this->assertTrue($constraint->evaluate('foo'));
        $this->assertFalse($constraint->evaluate('prefixfoo'));
        $this->assertEquals('starts not with "prefix"', $constraint->toString());

        try {
            $constraint->fail('prefixfoo', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <string:prefixfoo> starts not with "prefix".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringStartsWith
     * @covers PHPUnit_Framework_Assert::stringStartsWith
     */
    public function testConstraintStringStartsNotWith2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringStartsWith('prefix')
        );

        try {
            $constraint->fail('prefixfoo', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:prefixfoo> starts not with \"prefix\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringContains
     * @covers PHPUnit_Framework_Assert::stringContains
     */
    public function testConstraintStringContains()
    {
        $constraint = PHPUnit_Framework_Assert::stringContains('foo');

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
     * @covers PHPUnit_Framework_Assert::stringContains
     */
    public function testConstraintStringContains2()
    {
        $constraint = PHPUnit_Framework_Assert::stringContains('foo');

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
     * @covers PHPUnit_Framework_Assert::stringContains
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintStringNotContains()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringContains('foo')
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
     * @covers PHPUnit_Framework_Assert::stringContains
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintStringNotContains2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringContains('foo')
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
     * @covers PHPUnit_Framework_Constraint_StringEndsWith
     * @covers PHPUnit_Framework_Assert::stringEndsWith
     */
    public function testConstraintStringEndsWith()
    {
        $constraint = PHPUnit_Framework_Assert::stringEndsWith('suffix');

        $this->assertFalse($constraint->evaluate('foo'));
        $this->assertTrue($constraint->evaluate('foosuffix'));
        $this->assertEquals('ends with "suffix"', $constraint->toString());

        try {
            $constraint->fail('foo', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <string:foo> ends with "suffix".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringEndsWith
     * @covers PHPUnit_Framework_Assert::stringEndsWith
     */
    public function testConstraintStringEndsWith2()
    {
        $constraint = PHPUnit_Framework_Assert::stringEndsWith('suffix');

        try {
            $constraint->fail('foo', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:foo> ends with \"suffix\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringEndsWith
     * @covers PHPUnit_Framework_Constraint_Not
     * @covers PHPUnit_Framework_Assert::stringEndsWith
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintStringEndsNotWith()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringEndsWith('suffix')
        );

        $this->assertTrue($constraint->evaluate('foo'));
        $this->assertFalse($constraint->evaluate('foosuffix'));
        $this->assertEquals('ends not with "suffix"', $constraint->toString());

        try {
            $constraint->fail('foosuffix', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              'Failed asserting that <string:foosuffix> ends not with "suffix".',
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_StringEndsWith
     * @covers PHPUnit_Framework_Assert::stringEndsWith
     */
    public function testConstraintStringEndsNotWith2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
          PHPUnit_Framework_Assert::stringEndsWith('suffix')
        );

        try {
            $constraint->fail('foosuffix', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:foosuffix> ends not with \"suffix\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     */
    public function testConstraintArrayContains()
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
    public function testConstraintArrayContains2()
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
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintArrayNotContains()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
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
     * @covers PHPUnit_Framework_Assert::logicalNot
     */
    public function testConstraintArrayNotContains2()
    {
        $constraint = PHPUnit_Framework_Assert::logicalNot(
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

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     */
    public function testConstraintSplObjectStorageContains()
    {
        $object     = new StdClass;
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains($object);
        $this->assertEquals("contains \nstdClass Object\n(\n)\n", $constraint->toString());

        $storage = new SplObjectStorage;
        $this->assertFalse($constraint->evaluate($storage));

        $storage->attach($object);
        $this->assertTrue($constraint->evaluate($storage));

        try {
            $constraint->fail(new SplObjectStorage, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that an iterator contains \nstdClass Object\n(\n)\n.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit_Framework_Constraint_TraversableContains
     */
    public function testConstraintSplObjectStorageContains2()
    {
        $object     = new StdClass;
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains($object);

        try {
            $constraint->fail(new SplObjectStorage, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that an iterator contains \nstdClass Object\n(\n)\n.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }
}
?>
