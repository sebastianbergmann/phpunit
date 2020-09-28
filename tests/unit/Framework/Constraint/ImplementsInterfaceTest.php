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

use Exception;
use Iterator;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\TestFixture\ExampleTrait;
use Throwable;
use Traversable;

/**
 * @small
 */
final class ImplementsInterfaceTest extends ConstraintTestCase
{
    public static function implementsInterfaceProvider(): array
    {
        return [
            // class implements interface
            [
                'interface' => Throwable::class,
                'subject'   => Exception::class,
            ],

            // object of class that implements interface
            [
                'interface' => Throwable::class,
                'subject'   => new Exception(),
            ],

            // interface that extends interface
            [
                'interface' => Traversable::class,
                'subject'   => Iterator::class,
            ],

        ];
    }

    public static function notImplementsInterfaceProvider(): array
    {
        $template = 'Failed asserting that %s implements interface %s.';

        return [
            [
                'interface' => Traversable::class,
                'subject'   => Exception::class,
                'message'   => sprintf($template, Exception::class, Traversable::class),
            ],
            [
                'interface' => Traversable::class,
                'subject'   => new Exception(),
                'message'   => sprintf($template, 'object ' . Exception::class, Traversable::class),
            ],
            [
                'interface' => Traversable::class,
                'subject'   => 'lorem ipsum',
                'message'   => sprintf($template, "'lorem ipsum'", Traversable::class),
            ],
            [
                'interface' => Traversable::class,
                'subject'   => 123,
                'message'   => sprintf($template, '123', Traversable::class),
            ],
        ];
    }

    public static function constraintThrowsInvalidArgumentExceptionProvider(): array
    {
        $message = '/Argument #1 of \S+ must be an interface-string/';

        return [
            [
                'argument' => 'non-interface string',
                'messsage' => $message,
            ],

            [
                'argument' => Exception::class,
                'messsage' => $message,
            ],

            [
                'argument' => ExampleTrait::class,
                'messsage' => $message,
            ],
        ];
    }

    /**
     * @dataProvider implementsInterfaceProvider
     */
    public function testConstraintSucceeds(string $interface, $subject): void
    {
        $constraint = ImplementsInterface::fromInterfaceString($interface);

        self::assertTrue($constraint->evaluate($subject, '', true));
    }

    /**
     * @dataProvider notImplementsInterfaceProvider
     */
    public function testConstraintFails(string $interface, $subject, string $message): void
    {
        $constraint = ImplementsInterface::fromInterfaceString($interface);

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

        ImplementsInterface::fromInterfaceString($argument);
    }

    public function testFailureDescriptionOfCustomUnaryOperator(): void
    {
        $constraint = ImplementsInterface::fromInterfaceString(Throwable::class);

        $noop = $this->getMockBuilder(UnaryOperator::class)
            ->setConstructorArgs([$constraint])
            ->getMockForAbstractClass();

        $noop->expects($this->any())
            ->method('operator')
            ->willReturn('noop');
        $noop->expects($this->any())
            ->method('precedence')
            ->willReturn(1);

        $regexp = '/Iterator implements interface Throwable/';

        self::expectException(ExpectationFailedException::class);
        self::expectExceptionMessageMatches($regexp);

        $noop->evaluate(Iterator::class);
    }
}
