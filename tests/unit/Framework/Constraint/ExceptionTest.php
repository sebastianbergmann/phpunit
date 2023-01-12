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

use function sprintf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\DummyException;
use PHPUnit\Util\Filter;
use PHPUnit\Util\ThrowableToStringMapper;

#[CoversClass(Exception::class)]
#[Small]
final class ExceptionTest extends TestCase
{
    public function testExceptionCanBeExportedAsString(): void
    {
        $exception = new Exception(Exception::class);

        $this->assertSame('exception of type "' . Exception::class . '"', $exception->toString());
    }

    public function testConstraintException(): void
    {
        $constraint = new Exception('FoobarException');
        $exception  = new DummyException('Test');
        $stackTrace = Filter::getFilteredStacktrace($exception);

        try {
            $constraint->evaluate($exception);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                sprintf(
                    <<<EOF
Failed asserting that exception of type "%s" matches expected exception "FoobarException". Message was: "Test" at
{$stackTrace}.

EOF
                    ,
                    DummyException::class
                ),
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }
}
