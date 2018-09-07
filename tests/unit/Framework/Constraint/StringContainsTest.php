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

class StringContainsTest extends ConstraintTestCase
{
    public function testConstraintStringContains()
    {
        $constraint = new StringContains('foo');

        $this->assertFalse($constraint->evaluate('barbazbar', '', true));
        $this->assertTrue($constraint->evaluate('barfoobar', '', true));
        $this->assertEquals('contains "foo"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('barbazbar');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'barbazbar' contains "foo".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringContainsWhenIgnoreCase()
    {
        $constraint = new StringContains('oryginał', true);

        $this->assertFalse($constraint->evaluate('oryginal', '', true));
        $this->assertTrue($constraint->evaluate('ORYGINAŁ', '', true));
        $this->assertTrue($constraint->evaluate('oryginał', '', true));
        $this->assertEquals('contains "oryginał"', $constraint->toString());
        $this->assertEquals(1, \count($constraint));

        $this->expectException(ExpectationFailedException::class);

        $constraint->evaluate('oryginal');
    }

    public function testConstraintStringContainsForUtf8StringWhenNotIgnoreCase()
    {
        $constraint = new StringContains('oryginał', false);

        $this->assertFalse($constraint->evaluate('oryginal', '', true));
        $this->assertFalse($constraint->evaluate('ORYGINAŁ', '', true));
        $this->assertTrue($constraint->evaluate('oryginał', '', true));
        $this->assertEquals('contains "oryginał"', $constraint->toString());
        $this->assertEquals(1, \count($constraint));

        $this->expectException(ExpectationFailedException::class);

        $constraint->evaluate('oryginal');
    }

    public function testConstraintStringContains2()
    {
        $constraint = new StringContains('foo');

        try {
            $constraint->evaluate('barbazbar', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'barbazbar' contains "foo".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
