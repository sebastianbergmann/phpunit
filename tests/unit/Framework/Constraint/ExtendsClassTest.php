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

use Error;
use ErrorException;
use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\TestFixture\ExampleTrait;
use Throwable;

/**
 * @small
 */
final class ExtendsClassTest extends ConstraintTestCase
{
    public static function extendsClassProvider(): array
    {
        return [
            // class extends class
            [
                'class'   => Exception::class,
                'subject' => ErrorException::class,
            ],

            // object of class that extends class
            [
                'class'   => Exception::class,
                'subject' => new ErrorException(),
            ],
        ];
    }

    public static function notExtendsClassProvider(): array
    {
        $template = 'Failed asserting that %s extends class %s.';

        return [
            [
                'class'   => Error::class,
                'subject' => ErrorException::class,
                'message' => sprintf($template, ErrorException::class, Error::class),
            ],
            [
                'class'   => Error::class,
                'subject' => new ErrorException(),
                'message' => sprintf($template, 'object ' . ErrorException::class, Error::class),
            ],
            [
                'class'   => Error::class,
                'subject' => 'lorem ipsum',
                'message' => sprintf($template, "'lorem ipsum'", Error::class),
            ],
            [
                'class'   => Error::class,
                'subject' => 123,
                'message' => sprintf($template, '123', Error::class),
            ],
        ];
    }

    public static function constraintThrowsInvalidArgumentExceptionProvider(): array
    {
        $message = '/Argument #1 of \S+ must be a class-string/';

        return [
            [
                'argument' => 'non-class string',
                'messsage' => $message,
            ],

            [
                'argument' => Throwable::class,
                'messsage' => $message,
            ],

            [
                'argument' => ExampleTrait::class,
                'messsage' => $message,
            ],
        ];
    }

    /**
     * @dataProvider extendsClassProvider
     */
    public function testConstraintSucceeds(string $class, $subject): void
    {
        $constraint = ExtendsClass::fromClassString($class);

        self::assertTrue($constraint->evaluate($subject, '', true));
    }

    /**
     * @dataProvider notExtendsClassProvider
     */
    public function testConstraintFails(string $class, $subject, string $message): void
    {
        $constraint = ExtendsClass::fromClassString($class);

        self::expectException(ExpectationFailedException::class);
        self::expectExceptionMessage($message);

        $constraint->evaluate($subject);
    }

    /**
     * @dataProvider constraintThrowsInvalidArgumentExceptionProvider
     */
    public function testConstraintThrowsInvalidArgumentException(string $argument, string $message): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessageMatches($message);

        ExtendsClass::fromClassString($argument);
    }

    public function testFailureDescriptionOfCustomUnaryOperator(): void
    {
        $constraint = ExtendsClass::fromClassString(ErrorException::class);

        $noop = $this->getMockBuilder(UnaryOperator::class)
            ->setConstructorArgs([$constraint])
            ->getMockForAbstractClass();

        $noop->expects($this->any())
            ->method('operator')
            ->willReturn('noop');
        $noop->expects($this->any())
            ->method('precedence')
            ->willReturn(1);

        $regexp = '/Exception extends class ErrorException/';

        self::expectException(ExpectationFailedException::class);
        self::expectExceptionMessageMatches($regexp);

        $noop->evaluate(Exception::class);
    }
}
