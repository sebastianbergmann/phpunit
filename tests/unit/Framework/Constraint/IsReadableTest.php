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
final class IsReadableTest extends ConstraintTestCase
{
    public function testConstraintIsReadable(): void
    {
        $constraint = new IsReadable;

        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertEquals('is readable', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that "foo" is readable.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
