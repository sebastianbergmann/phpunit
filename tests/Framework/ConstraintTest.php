<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

use PHPUnit\Framework\Constraint\Count;
use PHPUnit\Framework\Constraint\SameSize;
use PHPUnit\Framework\Constraint\TraversableContains;
use PHPUnit\Util\Filter;

class ConstraintTest extends TestCase
{
    public function testConstraintArrayNotHasKey()
    {
        $constraint = Assert::logicalNot(
            Assert::arrayHasKey(0)
        );

        $this->assertFalse($constraint->evaluate([0 => 1], '', true));
        $this->assertEquals('does not have the key 0', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([0 => 1]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array does not have the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayNotHasKey2()
    {
        $constraint = Assert::logicalNot(
            Assert::arrayHasKey(0)
        );

        try {
            $constraint->evaluate([0], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that an array does not have the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintFileNotExists()
    {
        $file = \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';

        $constraint = Assert::logicalNot(
            Assert::fileExists()
        );

        $this->assertFalse($constraint->evaluate($file, '', true));
        $this->assertEquals('file does not exist', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($file);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that file "$file" does not exist.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintFileNotExists2()
    {
        $file = \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithNonPublicAttributes.php';

        $constraint = Assert::logicalNot(
            Assert::fileExists()
        );

        try {
            $constraint->evaluate($file, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that file "$file" does not exist.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotGreaterThan()
    {
        $constraint = Assert::logicalNot(
            Assert::greaterThan(1)
        );

        $this->assertTrue($constraint->evaluate(1, '', true));
        $this->assertEquals('is not greater than 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(2);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 2 is not greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotGreaterThan2()
    {
        $constraint = Assert::logicalNot(
            Assert::greaterThan(1)
        );

        try {
            $constraint->evaluate(2, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 2 is not greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintGreaterThanOrEqual()
    {
        $constraint = Assert::greaterThanOrEqual(1);

        $this->assertTrue($constraint->evaluate(1, '', true));
        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertEquals('is equal to 1 or is greater than 1', $constraint->toString());
        $this->assertCount(2, $constraint);

        try {
            $constraint->evaluate(0);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 0 is equal to 1 or is greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintGreaterThanOrEqual2()
    {
        $constraint = Assert::greaterThanOrEqual(1);

        try {
            $constraint->evaluate(0, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 0 is equal to 1 or is greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotGreaterThanOrEqual()
    {
        $constraint = Assert::logicalNot(
            Assert::greaterThanOrEqual(1)
        );

        $this->assertFalse($constraint->evaluate(1, '', true));
        $this->assertEquals('not( is equal to 1 or is greater than 1 )', $constraint->toString());
        $this->assertCount(2, $constraint);

        try {
            $constraint->evaluate(1);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that not( 1 is equal to 1 or is greater than 1 ).

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotGreaterThanOrEqual2()
    {
        $constraint = Assert::logicalNot(
            Assert::greaterThanOrEqual(1)
        );

        try {
            $constraint->evaluate(1, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that not( 1 is equal to 1 or is greater than 1 ).

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsAnything()
    {
        $constraint = Assert::anything();

        $this->assertTrue($constraint->evaluate(null, '', true));
        $this->assertNull($constraint->evaluate(null));
        $this->assertEquals('is anything', $constraint->toString());
        $this->assertCount(0, $constraint);
    }

    public function testConstraintNotIsAnything()
    {
        $constraint = Assert::logicalNot(
            Assert::anything()
        );

        $this->assertFalse($constraint->evaluate(null, '', true));
        $this->assertEquals('is not anything', $constraint->toString());
        $this->assertCount(0, $constraint);

        try {
            $constraint->evaluate(null);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that null is not anything.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotEqual()
    {
        $constraint = Assert::logicalNot(
            Assert::equalTo(1)
        );

        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertFalse($constraint->evaluate(1, '', true));
        $this->assertEquals('is not equal to 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(1);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 1 is not equal to 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotEqual2()
    {
        $constraint = Assert::logicalNot(
            Assert::equalTo(1)
        );

        try {
            $constraint->evaluate(1, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 1 is not equal to 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical()
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $constraint = Assert::logicalNot(
            Assert::identicalTo($a)
        );

        $this->assertTrue($constraint->evaluate($b, '', true));
        $this->assertFalse($constraint->evaluate($a, '', true));
        $this->assertEquals('is not identical to an object of class "stdClass"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($a);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that two variables don't reference the same object.

EOF
                ,
                $this->trimnl(TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical2()
    {
        $a = new \stdClass;

        $constraint = Assert::logicalNot(
            Assert::identicalTo($a)
        );

        try {
            $constraint->evaluate($a, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that two variables don't reference the same object.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical3()
    {
        $constraint = Assert::logicalNot(
            Assert::identicalTo('a')
        );

        try {
            $constraint->evaluate('a', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that two strings are not identical.

EOF
                ,
                $this->trimnl(TestFailure::exceptionToString($e))
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsInstanceOf()
    {
        $constraint = Assert::isInstanceOf(\Exception::class);

        $this->assertFalse($constraint->evaluate(new \stdClass, '', true));
        $this->assertTrue($constraint->evaluate(new \Exception, '', true));
        $this->assertEquals('is instance of class "Exception"', $constraint->toString());
        $this->assertCount(1, $constraint);

        $interfaceConstraint = Assert::isInstanceOf(\Countable::class);
        $this->assertFalse($interfaceConstraint->evaluate(new \stdClass, '', true));
        $this->assertTrue($interfaceConstraint->evaluate(new \ArrayObject, '', true));
        $this->assertEquals('is instance of interface "Countable"', $interfaceConstraint->toString());

        try {
            $constraint->evaluate(new \stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that stdClass Object () is an instance of class "Exception".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsInstanceOf2()
    {
        $constraint = Assert::isInstanceOf(\Exception::class);

        try {
            $constraint->evaluate(new \stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that stdClass Object () is an instance of class "Exception".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotInstanceOf()
    {
        $constraint = Assert::logicalNot(
            Assert::isInstanceOf(\stdClass::class)
        );

        $this->assertFalse($constraint->evaluate(new \stdClass, '', true));
        $this->assertTrue($constraint->evaluate(new Exception, '', true));
        $this->assertEquals('is not instance of class "stdClass"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(new \stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that stdClass Object () is not an instance of class "stdClass".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotInstanceOf2()
    {
        $constraint = Assert::logicalNot(
            Assert::isInstanceOf(\stdClass::class)
        );

        try {
            $constraint->evaluate(new \stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that stdClass Object () is not an instance of class "stdClass".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotType()
    {
        $constraint = Assert::logicalNot(
            Assert::isType('string')
        );

        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertFalse($constraint->evaluate('', '', true));
        $this->assertEquals('is not of type "string"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that '' is not of type "string".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotType2()
    {
        $constraint = Assert::logicalNot(
            Assert::isType('string')
        );

        try {
            $constraint->evaluate('', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that '' is not of type "string".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotNull()
    {
        $constraint = Assert::logicalNot(
            Assert::isNull()
        );

        $this->assertFalse($constraint->evaluate(null, '', true));
        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertEquals('is not null', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(null);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that null is not null.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotNull2()
    {
        $constraint = Assert::logicalNot(
            Assert::isNull()
        );

        try {
            $constraint->evaluate(null, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that null is not null.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotLessThan()
    {
        $constraint = Assert::logicalNot(
            Assert::lessThan(1)
        );

        $this->assertTrue($constraint->evaluate(1, '', true));
        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertEquals('is not less than 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(0);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 0 is not less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotLessThan2()
    {
        $constraint = Assert::logicalNot(
            Assert::lessThan(1)
        );

        try {
            $constraint->evaluate(0, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 0 is not less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintLessThanOrEqual()
    {
        $constraint = Assert::lessThanOrEqual(1);

        $this->assertTrue($constraint->evaluate(1, '', true));
        $this->assertFalse($constraint->evaluate(2, '', true));
        $this->assertEquals('is equal to 1 or is less than 1', $constraint->toString());
        $this->assertCount(2, $constraint);

        try {
            $constraint->evaluate(2);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 2 is equal to 1 or is less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintLessThanOrEqual2()
    {
        $constraint = Assert::lessThanOrEqual(1);

        try {
            $constraint->evaluate(2, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 2 is equal to 1 or is less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotLessThanOrEqual()
    {
        $constraint = Assert::logicalNot(
            Assert::lessThanOrEqual(1)
        );

        $this->assertTrue($constraint->evaluate(2, '', true));
        $this->assertFalse($constraint->evaluate(1, '', true));
        $this->assertEquals('not( is equal to 1 or is less than 1 )', $constraint->toString());
        $this->assertCount(2, $constraint);

        try {
            $constraint->evaluate(1);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that not( 1 is equal to 1 or is less than 1 ).

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotLessThanOrEqual2()
    {
        $constraint = Assert::logicalNot(
            Assert::lessThanOrEqual(1)
        );

        try {
            $constraint->evaluate(1, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that not( 1 is equal to 1 or is less than 1 ).

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassNotHasAttribute()
    {
        $constraint = Assert::logicalNot(
            Assert::classHasAttribute('privateAttribute')
        );

        $this->assertTrue($constraint->evaluate(\stdClass::class, '', true));
        $this->assertFalse($constraint->evaluate(\ClassWithNonPublicAttributes::class, '', true));
        $this->assertEquals('does not have attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(\ClassWithNonPublicAttributes::class);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassNotHasAttribute2()
    {
        $constraint = Assert::logicalNot(
            Assert::classHasAttribute('privateAttribute')
        );

        try {
            $constraint->evaluate(\ClassWithNonPublicAttributes::class, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassNotHasStaticAttribute()
    {
        $constraint = Assert::logicalNot(
            Assert::classHasStaticAttribute('privateStaticAttribute')
        );

        $this->assertTrue($constraint->evaluate(\stdClass::class, '', true));
        $this->assertFalse($constraint->evaluate(\ClassWithNonPublicAttributes::class, '', true));
        $this->assertEquals('does not have static attribute "privateStaticAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(\ClassWithNonPublicAttributes::class);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that class "ClassWithNonPublicAttributes" does not have static attribute "privateStaticAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassNotHasStaticAttribute2()
    {
        $constraint = Assert::logicalNot(
            Assert::classHasStaticAttribute('privateStaticAttribute')
        );

        try {
            $constraint->evaluate(\ClassWithNonPublicAttributes::class, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that class "ClassWithNonPublicAttributes" does not have static attribute "privateStaticAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintObjectNotHasAttribute()
    {
        $constraint = Assert::logicalNot(
            Assert::objectHasAttribute('privateAttribute')
        );

        $this->assertTrue($constraint->evaluate(new \stdClass, '', true));
        $this->assertFalse($constraint->evaluate(new \ClassWithNonPublicAttributes, '', true));
        $this->assertEquals('does not have attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(new \ClassWithNonPublicAttributes);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that object of class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintObjectNotHasAttribute2()
    {
        $constraint = Assert::logicalNot(
            Assert::objectHasAttribute('privateAttribute')
        );

        try {
            $constraint->evaluate(new \ClassWithNonPublicAttributes, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that object of class "ClassWithNonPublicAttributes" does not have attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintPCRENotMatch()
    {
        $constraint = Assert::logicalNot(
            Assert::matchesRegularExpression('/foo/')
        );

        $this->assertTrue($constraint->evaluate('barbazbar', '', true));
        $this->assertFalse($constraint->evaluate('barfoobar', '', true));
        $this->assertEquals('does not match PCRE pattern "/foo/"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('barfoobar');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'barfoobar' does not match PCRE pattern "/foo/".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintPCRENotMatch2()
    {
        $constraint = Assert::logicalNot(
            Assert::matchesRegularExpression('/foo/')
        );

        try {
            $constraint->evaluate('barfoobar', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'barfoobar' does not match PCRE pattern "/foo/".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringStartsNotWith()
    {
        $constraint = Assert::logicalNot(
            Assert::stringStartsWith('prefix')
        );

        $this->assertTrue($constraint->evaluate('foo', '', true));
        $this->assertFalse($constraint->evaluate('prefixfoo', '', true));
        $this->assertEquals('starts not with "prefix"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('prefixfoo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'prefixfoo' starts not with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringStartsNotWith2()
    {
        $constraint = Assert::logicalNot(
            Assert::stringStartsWith('prefix')
        );

        try {
            $constraint->evaluate('prefixfoo', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'prefixfoo' starts not with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringNotContains()
    {
        $constraint = Assert::logicalNot(
            Assert::stringContains('foo')
        );

        $this->assertTrue($constraint->evaluate('barbazbar', '', true));
        $this->assertFalse($constraint->evaluate('barfoobar', '', true));
        $this->assertEquals('does not contain "foo"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('barfoobar');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'barfoobar' does not contain "foo".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringNotContainsWhenIgnoreCase()
    {
        $constraint = Assert::logicalNot(
            Assert::stringContains('oryginał')
        );

        $this->assertTrue($constraint->evaluate('original', '', true));
        $this->assertFalse($constraint->evaluate('ORYGINAŁ', '', true));
        $this->assertFalse($constraint->evaluate('oryginał', '', true));
        $this->assertEquals('does not contain "oryginał"', $constraint->toString());
        $this->assertEquals(1, \count($constraint));

        $this->expectException(ExpectationFailedException::class);

        $constraint->evaluate('ORYGINAŁ');
    }

    public function testConstraintStringNotContainsForUtf8StringWhenNotIgnoreCase()
    {
        $constraint = Assert::logicalNot(
            Assert::stringContains('oryginał', false)
        );

        $this->assertTrue($constraint->evaluate('original', '', true));
        $this->assertTrue($constraint->evaluate('ORYGINAŁ', '', true));
        $this->assertFalse($constraint->evaluate('oryginał', '', true));
        $this->assertEquals('does not contain "oryginał"', $constraint->toString());
        $this->assertEquals(1, \count($constraint));

        $this->expectException(ExpectationFailedException::class);

        $constraint->evaluate('oryginał');
    }

    public function testConstraintStringNotContains2()
    {
        $constraint = Assert::logicalNot(
            Assert::stringContains('foo')
        );

        try {
            $constraint->evaluate('barfoobar', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'barfoobar' does not contain "foo".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsNotWith()
    {
        $constraint = Assert::logicalNot(
            Assert::stringEndsWith('suffix')
        );

        $this->assertTrue($constraint->evaluate('foo', '', true));
        $this->assertFalse($constraint->evaluate('foosuffix', '', true));
        $this->assertEquals('ends not with "suffix"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foosuffix');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'foosuffix' ends not with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsNotWith2()
    {
        $constraint = Assert::logicalNot(
            Assert::stringEndsWith('suffix')
        );

        try {
            $constraint->evaluate('foosuffix', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'foosuffix' ends not with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayNotContains()
    {
        $constraint = Assert::logicalNot(
            new TraversableContains('foo')
        );

        $this->assertTrue($constraint->evaluate(['bar'], '', true));
        $this->assertFalse($constraint->evaluate(['foo'], '', true));
        $this->assertEquals("does not contain 'foo'", $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(['foo']);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array does not contain 'foo'.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayNotContains2()
    {
        $constraint = Assert::logicalNot(
            new TraversableContains('foo')
        );

        try {
            $constraint->evaluate(['foo'], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that an array does not contain 'foo'.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testAttributeNotEqualTo()
    {
        $object     = new \ClassWithNonPublicAttributes;
        $constraint = Assert::logicalNot(
            Assert::attributeEqualTo('foo', 2)
        );

        $this->assertTrue($constraint->evaluate($object, '', true));
        $this->assertEquals('attribute "foo" is not equal to 2', $constraint->toString());
        $this->assertCount(1, $constraint);

        $constraint = Assert::logicalNot(
            Assert::attributeEqualTo('foo', 1)
        );

        $this->assertFalse($constraint->evaluate($object, '', true));

        try {
            $constraint->evaluate($object);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that attribute "foo" is not equal to 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testAttributeNotEqualTo2()
    {
        $object     = new \ClassWithNonPublicAttributes;
        $constraint = Assert::logicalNot(
            Assert::attributeEqualTo('foo', 1)
        );

        try {
            $constraint->evaluate($object, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that attribute "foo" is not equal to 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintCountWithAnArray()
    {
        $constraint = new Count(5);

        $this->assertTrue($constraint->evaluate([1, 2, 3, 4, 5], '', true));
        $this->assertFalse($constraint->evaluate([1, 2, 3, 4], '', true));
    }

    public function testConstraintCountWithAnIteratorWhichDoesNotImplementCountable()
    {
        $constraint = new Count(5);

        $this->assertTrue($constraint->evaluate(new \TestIterator([1, 2, 3, 4, 5]), '', true));
        $this->assertFalse($constraint->evaluate(new \TestIterator([1, 2, 3, 4]), '', true));
    }

    public function testConstraintCountWithAnObjectImplementingCountable()
    {
        $constraint = new Count(5);

        $this->assertTrue($constraint->evaluate(new \ArrayObject([1, 2, 3, 4, 5]), '', true));
        $this->assertFalse($constraint->evaluate(new \ArrayObject([1, 2, 3, 4]), '', true));
    }

    public function testConstraintCountFailing()
    {
        $constraint = new Count(5);

        try {
            $constraint->evaluate([1, 2]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that actual size 2 matches expected size 5.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotCountFailing()
    {
        $constraint = Assert::logicalNot(
            new Count(2)
        );

        try {
            $constraint->evaluate([1, 2]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that actual size 2 does not match expected size 2.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintNotSameSizeFailing()
    {
        $constraint = Assert::logicalNot(
            new SameSize([1, 2])
        );

        try {
            $constraint->evaluate([3, 4]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that actual size 2 does not match expected size 2.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintException()
    {
        $constraint = new Constraint\Exception('FoobarException');
        $exception  = new \DummyException('Test');
        $stackTrace = Filter::getFilteredStacktrace($exception);

        try {
            $constraint->evaluate($exception);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that exception of type "DummyException" matches expected exception "FoobarException". Message was: "Test" at
$stackTrace.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * Removes spaces in front of newlines
     *
     * @param string $string
     *
     * @return string
     */
    private function trimnl($string)
    {
        return \preg_replace('/[ ]*\n/', "\n", $string);
    }
}
