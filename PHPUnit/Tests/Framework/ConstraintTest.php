<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework/TestCase.php';

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class Framework_ConstraintTest extends PHPUnit_Framework_TestCase
{
    public function testConstraintArrayHasKey()
    {
        $constraint = new PHPUnit_Framework_Constraint_ArrayHasKey(0);

        $this->assertFalse($constraint->evaluate(array()));
        $this->assertEquals('has key <integer:0>', $constraint->toString());

        try {
            $constraint->fail(array(), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nArray\n(\n)\n has key <integer:0>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayNotHasKey()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ArrayHasKey(0)
        );

        $this->assertTrue($constraint->evaluate(array()));
        $this->assertEquals('does not have key <integer:0>', $constraint->toString());

        try {
            $constraint->fail(array(0), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nArray\n(\n    [0] => 0\n)\n does not have key <integer:0>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

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

    public function testConstraintFileNotExists()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_FileExists
        );

        $this->assertTrue($constraint->evaluate('foo'));
        $this->assertEquals('file does not exist', $constraint->toString());

        try {
            $constraint->fail('foo', '');
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

    public function testConstraintNotGreaterThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_GreaterThan(1)
        );

        $this->assertTrue($constraint->evaluate(1));
        $this->assertEquals('is not greater than <integer:1>', $constraint->toString());

        try {
            $constraint->fail(1, '');
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

    public function testConstraintIsAnything()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsAnything;

        $this->assertTrue($constraint->evaluate(NULL));
        $this->assertNull($constraint->fail(NULL, ''));
        $this->assertEquals('is anything', $constraint->toString());
    }

    public function testConstraintNotIsAnything()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsAnything
        );

        $this->assertFalse($constraint->evaluate(NULL));
        $this->assertNull($constraint->fail(NULL, ''));
        $this->assertEquals('is not anything', $constraint->toString());
    }

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

    public function testConstraintIsNotEqual()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsEqual(1)
        );

        $this->assertTrue($constraint->evaluate(0));
        $this->assertFalse($constraint->evaluate(1));
        $this->assertEquals('is not equal to <integer:1>', $constraint->toString());

        try {
            $constraint->fail(1, '');
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

    public function testConstraintIsIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new PHPUnit_Framework_Constraint_IsIdentical($a);

        $this->assertFalse($constraint->evaluate($b));
        $this->assertTrue($constraint->evaluate($a));
        $this->assertEquals("is identical to \nstdClass Object\n(\n)\n", $constraint->toString());

        try {
            $constraint->fail($b, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nstdClass Object\n(\n)\n is identical to \nstdClass Object\n(\n)\n.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsIdentical($a)
        );

        $this->assertTrue($constraint->evaluate($b));
        $this->assertFalse($constraint->evaluate($a));
        $this->assertEquals("is not identical to \nstdClass Object\n(\n)\n", $constraint->toString());

        try {
            $constraint->fail($a, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nstdClass Object\n(\n)\n is not identical to \nstdClass Object\n(\n)\n.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

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
              "Failed asserting that \nstdClass Object\n(\n)\n is instance of class \"Exception\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotInstanceOf()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsInstanceOf('stdClass')
        );

        $this->assertFalse($constraint->evaluate(new stdClass));
        $this->assertTrue($constraint->evaluate(new Exception));
        $this->assertEquals('is not instance of class "stdClass"', $constraint->toString());

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nstdClass Object\n(\n)\n is not instance of class \"stdClass\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

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

    public function testConstraintIsNotType()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsType('string')
        );

        $this->assertTrue($constraint->evaluate(0));
        $this->assertFalse($constraint->evaluate(''));
        $this->assertEquals('is not of type "string"', $constraint->toString());

        try {
            $constraint->fail('', '');
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

    public function testConstraintNotLessThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_LessThan(1)
        );

        $this->assertTrue($constraint->evaluate(1));
        $this->assertEquals('is not less than <integer:1>', $constraint->toString());

        try {
            $constraint->fail(1, '');
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
              "Failed asserting that \nstdClass Object\n(\n)\n has attribute \"foo\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

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
            $constraint->fail($o, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nstdClass Object\n(\n    [foo] => bar\n)\n does not have attribute \"foo\".",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

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

    public function testConstraintPCRENotMatch()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_PCREMatch('/foo/')
        );

        $this->assertTrue($constraint->evaluate('barbazbar'));
        $this->assertFalse($constraint->evaluate('barfoobar'));
        $this->assertEquals('does not match PCRE pattern "/foo/"', $constraint->toString());

        try {
            $constraint->fail('barfoobar', '');
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

    public function testConstraintStringNotContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_StringContains('foo')
        );

        $this->assertTrue($constraint->evaluate('barbazbar'));
        $this->assertFalse($constraint->evaluate('barfoobar'));
        $this->assertEquals('does not contain "foo"', $constraint->toString());

        try {
            $constraint->fail('barfoobar', '');
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
              "Failed asserting that \nArray\n(\n    [0] => bar\n)\n contains <string:foo>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintTraversableNotContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_TraversableContains('foo')
        );

        $this->assertTrue($constraint->evaluate(array('bar')));
        $this->assertFalse($constraint->evaluate(array('foo')));
        $this->assertEquals('does not contain <string:foo>', $constraint->toString());

        try {
            $constraint->fail(array('foo'), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nArray\n(\n    [0] => foo\n)\n does not contain <string:foo>.",
              $e->getDescription()
            );

            return;
        }

        $this->fail();
    }
}
?>
