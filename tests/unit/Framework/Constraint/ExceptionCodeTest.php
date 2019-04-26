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
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;

class ExceptionCodeTest extends TestCase
{
    public function testExceptionCodeCanEvaluateExceptions(): void
    {
        $exceptionCode = new ExceptionCode(123);

        $other = new \Exception('bla', 456);

        try {
            $exceptionCode->evaluate($other);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 456 is equal to expected exception code 123.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }
    }

    public function testExceptionCodeCanBeExportedAsString(): void
    {
        $exceptionCode = new ExceptionCode(ExceptionCode::class);

        $this->assertSame('exception code is ', $exceptionCode->toString());
    }
}
