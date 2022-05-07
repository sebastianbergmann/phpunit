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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;

#[CoversClass(ArrayIsList::class)]
#[Small]
final class ArrayIsListTest extends ConstraintTestCase
{
    public function testConstraintArrayIsListWhenEmpty(): void
    {
        $constraint = new ArrayIsList;

        $this->assertTrue($constraint->evaluate([], '', true));
    }

    public function testConstraintArrayIsNotList(): void
    {
        $constraint = new ArrayIsList;

        $this->assertFalse($constraint->evaluate([1 => 1], '', true));
        $this->assertEquals('is list', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([1 => 1]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that an array is list.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayIsNotListWithFilteredArray(): void
    {
        $constraint = new ArrayIsList;

        $this->assertFalse($constraint->evaluate([0 => 0, 1 => 1, 3 => 3], '', true));
        $this->assertEquals('is list', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([1 => 1]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that an array is list.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayIsNotListWithCustomMessage(): void
    {
        $constraint = new ArrayIsList;

        try {
            $constraint->evaluate([1 => 1], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
custom message
Failed asserting that an array is list.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayIsNotListWhenNotArray(): void
    {
        $constraint = new ArrayIsList;

        try {
            $constraint->evaluate('not array');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that an array is list.

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
