<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.0
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.0
 */
class Framework_TestFailureTest extends PHPUnit_Framework_TestCase
{
    public function testFailureArrayHasKey()
    {
        $constraint = new PHPUnit_Framework_Constraint_ArrayHasKey(0);

        try {
            $constraint->fail(array(), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that an array has the key <integer:0>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureArrayHasKey2()
    {
        $constraint = new PHPUnit_Framework_Constraint_ArrayHasKey(0);

        try {
            $constraint->fail(array(), 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that an array has the key <integer:0>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureArrayNotHasKey()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ArrayHasKey(0)
        );

        try {
            $constraint->fail(array(0), '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that an array does not have the key <integer:0>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureArrayNotHasKey2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ArrayHasKey(0)
        );

        try {
            $constraint->fail(array(0), 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that an array does not have the key <integer:0>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureFileExists()
    {
        $constraint = new PHPUnit_Framework_Constraint_FileExists;

        try {
            $constraint->fail('foo', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that file \"foo\" exists.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureFileExists2()
    {
        $constraint = new PHPUnit_Framework_Constraint_FileExists;

        try {
            $constraint->fail('foo', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that file \"foo\" exists.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureFileNotExists()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_FileExists
        );

        try {
            $constraint->fail('foo', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that file \"foo\" does not exist.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureFileNotExists2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_FileExists
        );

        try {
            $constraint->fail('foo', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that file \"foo\" does not exist.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureGreaterThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_GreaterThan(1);

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:0> is greater than <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureGreaterThan2()
    {
        $constraint = new PHPUnit_Framework_Constraint_GreaterThan(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:0> is greater than <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureNotGreaterThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_GreaterThan(1)
        );

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:1> is not greater than <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureNotGreaterThan2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_GreaterThan(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:1> is not greater than <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsEqual()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsEqual(1);

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:0> matches expected <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsEqual2()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsEqual(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:0> matches expected <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsNotEqual()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsEqual(1)
        );

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:1> is not equal to <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsNotEqual2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsEqual(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:1> is not equal to <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new PHPUnit_Framework_Constraint_IsIdentical($a);

        try {
            $constraint->fail($b, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that two variables reference the same object.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsIdentical2()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new PHPUnit_Framework_Constraint_IsIdentical($a);

        try {
            $constraint->fail($b, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that two variables reference the same object.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsNotIdentical()
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsIdentical($a)
        );

        try {
            $constraint->fail($a, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nstdClass Object\n(\n)\n is not identical to an object of class \"stdClass\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsNotIdentical2()
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
              "custom message\nFailed asserting that \nstdClass Object\n(\n)\n is not identical to an object of class \"stdClass\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsInstanceOf()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsInstanceOf('Exception');

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <stdClass> is an instance of class \"Exception\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsInstanceOf2()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsInstanceOf('Exception');

        try {
            $constraint->fail(new stdClass, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <stdClass> is an instance of class \"Exception\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsNotInstanceOf()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsInstanceOf('stdClass')
        );

        try {
            $constraint->fail(new stdClass, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <stdClass> is not an instance of class \"stdClass\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsNotInstanceOf2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsInstanceOf('stdClass')
        );

        try {
            $constraint->fail(new stdClass, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <stdClass> is not an instance of class \"stdClass\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsType()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsType('string');

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that \nstdClass Object\n(\n)\n is of type \"string\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsType2()
    {
        $constraint = new PHPUnit_Framework_Constraint_IsType('string');

        try {
            $constraint->fail(new stdClass, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that \nstdClass Object\n(\n)\n is of type \"string\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsNotType()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsType('string')
        );

        try {
            $constraint->fail('', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <string:> is not of type \"string\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureIsNotType2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsType('string')
        );

        try {
            $constraint->fail('', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:> is not of type \"string\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureLessThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_LessThan(1);

        try {
            $constraint->fail(0, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:0> is less than <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureLessThan2()
    {
        $constraint = new PHPUnit_Framework_Constraint_LessThan(1);

        try {
            $constraint->fail(0, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:0> is less than <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureNotLessThan()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_LessThan(1)
        );

        try {
            $constraint->fail(1, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <integer:1> is not less than <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureNotLessThan2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_LessThan(1)
        );

        try {
            $constraint->fail(1, 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <integer:1> is not less than <integer:1>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureObjectHasAttribute()
    {
        $constraint = new PHPUnit_Framework_Constraint_ObjectHasAttribute('foo');

        try {
            $constraint->fail(new stdClass, '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that object of class \"stdClass\" has attribute \"foo\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureObjectHasAttribute2()
    {
        $constraint = new PHPUnit_Framework_Constraint_ObjectHasAttribute('foo');

        try {
            $constraint->fail(new stdClass, 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that object of class \"stdClass\" has attribute \"foo\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureObjectNotHasAttribute()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ObjectHasAttribute('foo')
        );

        $o = new stdClass;
        $o->foo = 'bar';

        try {
            $constraint->fail($o, '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that object of class \"stdClass\" does not have attribute \"foo\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureObjectNotHasAttribute2()
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
              "custom message\nFailed asserting that object of class \"stdClass\" does not have attribute \"foo\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailurePCREMatch()
    {
        $constraint = new PHPUnit_Framework_Constraint_PCREMatch('/foo/');

        try {
            $constraint->fail('barbazbar', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <string:barbazbar> matches PCRE pattern \"/foo/\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailurePCREMatch2()
    {
        $constraint = new PHPUnit_Framework_Constraint_PCREMatch('/foo/');

        try {
            $constraint->fail('barbazbar', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:barbazbar> matches PCRE pattern \"/foo/\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailurePCRENotMatch()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_PCREMatch('/foo/')
        );

        try {
            $constraint->fail('barfoobar', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <string:barfoobar> does not match PCRE pattern \"/foo/\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailurePCRENotMatch2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_PCREMatch('/foo/')
        );

        try {
            $constraint->fail('barfoobar', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:barfoobar> does not match PCRE pattern \"/foo/\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureStringContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_StringContains('foo');

        try {
            $constraint->fail('barbazbar', '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <string:barbazbar> contains \"foo\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureStringContains2()
    {
        $constraint = new PHPUnit_Framework_Constraint_StringContains('foo');

        try {
            $constraint->fail('barbazbar', 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:barbazbar> contains \"foo\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureStringNotContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_StringContains('foo')
        );

        try {
            $constraint->fail('barfoobar', '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that <string:barfoobar> does not contain \"foo\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureStringNotContains2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_StringContains('foo')
        );

        try {
            $constraint->fail('barfoobar', 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that <string:barfoobar> does not contain \"foo\".\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureTraversableContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains('foo');

        try {
            $constraint->fail(array('bar'), '');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that an array contains <string:foo>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureTraversableContains2()
    {
        $constraint = new PHPUnit_Framework_Constraint_TraversableContains('foo');

        try {
            $constraint->fail(array('bar'), 'custom message');
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that an array contains <string:foo>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureTraversableNotContains()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_TraversableContains('foo')
        );

        try {
            $constraint->fail(array('foo'), '', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "Failed asserting that an array does not contain <string:foo>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testFailureTraversableNotContains2()
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_TraversableContains('foo')
        );

        try {
            $constraint->fail(array('foo'), 'custom message', TRUE);
        }

        catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
              "custom message\nFailed asserting that an array does not contain <string:foo>.\n",
              PHPUnit_Framework_TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
