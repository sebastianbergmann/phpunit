<?php  declare(strict_types=1);

namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestCase;

class ExceptionCodeTest extends TestCase
{
    public function testExceptionCodeCanEvaluateExceptions()
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

    public function testExceptionCodeCanBeExportedAsString()
    {
        $exceptionCode = new ExceptionCode(ExceptionCode::class);

        $this->assertSame('exception code is ', $exceptionCode->toString());
    }
}
