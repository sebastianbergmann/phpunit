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
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;

class LogicalNotTest extends TestCase
{
    public function testNonRestrictedConstructParameterIsTreatedAsIsEqual(): void
    {
        $constraint = new LogicalNot('test');

        $this->assertSame('is not equal to \'test\'', $constraint->toString());
    }

    public function testConstraintIsNotEqualStringContainsDoubleQuotes(): void
    {
        $string     = 'a "b" c';
        $other      = 'a ""b"" c';
        $constraint = Assert::logicalNot(
            Assert::equalTo($string)
        );

        $this->assertTrue($constraint->evaluate($other, '', true));
        $this->assertFalse($constraint->evaluate($string, '', true));
        $this->assertEquals("is not equal to '{$string}'", $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($string);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that '{$string}' is not equal to '{$string}'.

EOF
                ,
                TestFailure::exceptionToString($e)    // Fails here
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotEqualStringContainsPositiveWords(): void
    {
        $string     = 'a is b';
        $other      = 'a certainly is b';
        $constraint = Assert::logicalNot(
            Assert::equalTo($string)
        );

        $this->assertTrue($constraint->evaluate($other, '', true));
        $this->assertFalse($constraint->evaluate($string, '', true));
        $this->assertEquals("is not equal to '{$string}'", $constraint->toString());  // Fails here
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($string);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that '{$string}' is not equal to '{$string}'.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
