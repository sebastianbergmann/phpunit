<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;

class TraversableContainsTest extends ConstraintTestCase
{
    public function testConstraintTraversableCheckForObjectIdentityForDefaultCase()
    {
        $constraint = new TraversableContains('foo');

        $this->assertTrue($constraint->evaluate([0], '', true));
        $this->assertTrue($constraint->evaluate([true], '', true));
    }

    public function testConstraintTraversableCheckForObjectIdentityForPrimitiveType()
    {
        $constraint = new TraversableContains('foo', true, true);

        $this->assertFalse($constraint->evaluate([0], '', true));
        $this->assertFalse($constraint->evaluate([true], '', true));
    }

    public function testConstraintTraversableWithRightValue()
    {
        $constraint = new TraversableContains('foo');

        $this->assertTrue($constraint->evaluate(['foo'], '', true));
    }

    public function testConstraintTraversableWithFailValue()
    {
        $constraint = new TraversableContains('foo');

        $this->assertFalse($constraint->evaluate(['bar'], '', true));
    }

    public function testConstraintTraversableCountMethods()
    {
        $constraint = new TraversableContains('foo');

        $this->assertCount(1, $constraint);
    }

    public function testConstraintTraversableEvaluateMethodWithFailExample()
    {
        $constraint = new TraversableContains('foo');

        try {
            $constraint->evaluate(['bar']);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array contains 'foo'.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }
        $this->fail();
    }

    public function testConstraintTraversableEvaluateMethodWithFailExampleWithCustomMessage()
    {
        $constraint = new TraversableContains('foo');

        try {
            $constraint->evaluate(['bar'], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that an array contains 'foo'.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintTraversableToStringMethodsWithStdClass()
    {
        $object     = new \stdClass;
        $constraint = new TraversableContains($object);
        $this->assertStringMatchesFormat('contains stdClass Object &%s ()', $constraint->toString());
    }

    public function testConstraintTraversableToStringMethods()
    {
        $constraint = new TraversableContains('foo');

        $this->assertEquals("contains 'foo'", $constraint->toString());
    }

    public function testConstraintTraversableToStringMethodsWithSplObjectStorage()
    {
        $object     = new \stdClass;
        $constraint = new TraversableContains($object);

        $storage = new \SplObjectStorage;
        $this->assertFalse($constraint->evaluate($storage, '', true));

        $storage->attach($object);
        $this->assertTrue($constraint->evaluate($storage, '', true));
    }

    public function testConstraintTraversableStdClassForFailSplObjectStorage()
    {
        $object     = new \stdClass;
        $constraint = new TraversableContains($object);

        try {
            $constraint->evaluate(new \SplObjectStorage);
        } catch (ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(
                <<<EOF
Failed asserting that a traversable contains stdClass Object &%x ().

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintTraversableStdClassForFailSplObjectStorageWithCustomMessage()
    {
        $object     = new \stdClass;
        $constraint = new TraversableContains($object);

        try {
            $constraint->evaluate(new \SplObjectStorage, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertStringMatchesFormat(
                <<<EOF
custom message
Failed asserting that a traversable contains stdClass Object &%x ().

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
