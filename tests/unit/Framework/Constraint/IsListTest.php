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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\ThrowableToStringMapper;

#[CoversClass(IsList::class)]
#[Small]
final class IsListTest extends ConstraintTestCase
{
    public function testConstraintIsListWhenEmpty(): void
    {
        $constraint = new IsList;

        $this->assertTrue($constraint->evaluate([], '', true));
    }

    public function testConstraintIsNotList(): void
    {
        $constraint = new IsList;

        $this->assertFalse($constraint->evaluate([1 => 1], '', true));
        $this->assertEquals('is a list', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([1 => 1]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that an array is a list.

EOF
                ,
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotListWithFilteredArray(): void
    {
        $constraint = new IsList;

        $this->assertFalse($constraint->evaluate([0 => 0, 1 => 1, 3 => 3], '', true));
        $this->assertEquals('is a list', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([1 => 1]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that an array is a list.

EOF
                ,
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotListWithCustomMessage(): void
    {
        $constraint = new IsList;

        try {
            $constraint->evaluate([1 => 1], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
custom message
Failed asserting that an array is a list.

EOF
                ,
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotListWhenNotArray(): void
    {
        $constraint = new IsList;

        try {
            $constraint->evaluate('not array');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that a string is a list.

EOF
                ,
                ThrowableToStringMapper::map($e)
            );
        }
    }

    public function testConstraintArrayIsNotList(): void
    {
        $constraint = Assert::logicalNot(
            Assert::isList()
        );

        try {
            $constraint->evaluate([0, 1, 2], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
custom message
Failed asserting that an array is not a list.

EOF
                ,
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }
}
