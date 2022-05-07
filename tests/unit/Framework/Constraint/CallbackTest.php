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

#[CoversClass(Callback::class)]
#[Small]
final class CallbackTest extends ConstraintTestCase
{
    public static function staticCallbackReturningTrue(): bool
    {
        return true;
    }

    public function callbackReturningTrue(): bool
    {
        return true;
    }

    public function testConstraintCallback(): void
    {
        $closureReflect = static function (mixed $parameter): mixed
        {
            return $parameter;
        };

        $closureWithoutParameter = static function (): bool
        {
            return true;
        };

        $constraint = new Callback($closureWithoutParameter);
        $this->assertTrue($constraint->evaluate('', '', true));

        $constraint = new Callback($closureReflect);
        $this->assertTrue($constraint->evaluate(true, '', true));
        $this->assertFalse($constraint->evaluate(false, '', true));

        $callback   = [$this, 'callbackReturningTrue'];
        $constraint = new Callback($callback);
        $this->assertTrue($constraint->evaluate(false, '', true));

        $callback   = [self::class, 'staticCallbackReturningTrue'];
        $constraint = new Callback($callback);
        $this->assertTrue($constraint->evaluate(null, '', true));

        $this->assertEquals('is accepted by specified callback', $constraint->toString());
    }

    public function testConstraintCallbackFailure(): void
    {
        $constraint = new Callback(
            static function (): bool
            {
                return false;
            }
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that \'This fails\' is accepted by specified callback.');

        $constraint->evaluate('This fails');
    }
}
