<?php declare(strict_types=1);
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

/**
 * @small
 */
final class RegularExpressionTest extends ConstraintTestCase
{
    public function testConstraintRegularExpression(): void
    {
        $constraint = new RegularExpression('/foo/');

        $this->assertFalse($constraint->evaluate('barbazbar', '', true));
        $this->assertTrue($constraint->evaluate('barfoobar', '', true));
        $this->assertEquals('matches PCRE pattern "/foo/"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('barbazbar');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'barbazbar' matches PCRE pattern "/foo/".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintRegularExpression2(): void
    {
        $constraint = new RegularExpression('/foo/');

        try {
            $constraint->evaluate('barbazbar', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'barbazbar' matches PCRE pattern "/foo/".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
