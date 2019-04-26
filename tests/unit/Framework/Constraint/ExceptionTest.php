<?php  declare(strict_types=1);

namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testExceptionCanBeExportedAsString()
    {
        $exception = new Exception(Exception::class);

        $this->assertSame('exception of type "' . Exception::class . '"', $exception->toString());
    }
}
