<?php
/*
 * This file is part of sebastian/phpunit-framework-constraint.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;

class StringStartsWithTest extends TestCase
{
    public function testConstraintStringStartsWith()
    {
        $constraint = new StringStartsWith('prefix');

        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertTrue($constraint->evaluate('prefixfoo', '', true));
        $this->assertEquals('starts with "prefix"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'foo' starts with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringStartsWith2()
    {
        $constraint = new StringStartsWith('prefix');

        try {
            $constraint->evaluate('foo', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that 'foo' starts with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
