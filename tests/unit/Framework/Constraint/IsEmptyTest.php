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
final class IsEmptyTest extends ConstraintTestCase
{
    public function testConstraintIsEmpty(): void
    {
        $constraint = new IsEmpty;

        $this->assertFalse($constraint->evaluate(['foo'], '', true));
        $this->assertTrue($constraint->evaluate([], '', true));
        $this->assertFalse($constraint->evaluate(new \ArrayObject(['foo']), '', true));
        $this->assertTrue($constraint->evaluate(new \ArrayObject([]), '', true));
        $this->assertEquals('is empty', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(['foo']);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array is empty.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsEmpty2(): void
    {
        $constraint = new IsEmpty;

        try {
            $constraint->evaluate(['foo'], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that an array is empty.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    /**
     * @ticket https://github.com/sebastianbergmann/phpunit/issues/3743
     */
    public function test_EmptyIterator_is_handled_correctly(): void
    {
        $constraint = new IsEmpty;

        $this->assertTrue($constraint->evaluate(new \EmptyIterator, '', true));
    }
}
