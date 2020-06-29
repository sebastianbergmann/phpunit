<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\AnInterface;
use ReflectionException;

/**
 * @covers \PHPUnit\Framework\MockObject\Invocation
 */
class InvocationTest extends TestCase
{
    /**
     * @return array[]
     */
    public function correctStrictTypesProvider(): array
    {
        return [
            [
                'emptyMethod',
                [],
                new class {
                    public function emptyMethod(): void
                    {
                    }
                },
            ],
            [
                'methodAcceptingInt',
                [123],
                new class {
                    public function methodAcceptingInt(int $value): void
                    {
                    }
                },
            ],
            [
                'methodAcceptingAnInterface',
                [
                    new class implements AnInterface {
                        public function doSomething(): void
                        {
                        }
                    },
                ],
                new class {
                    public function methodAcceptingAnInterface(AnInterface $value): void
                    {
                    }
                },
            ],
            [
                'methodWithDefaultValue',
                [],
                new class {
                    public function methodWithDefaultValue(bool $value = true): void
                    {
                    }
                },
            ],
            [
                'nonTypedMethod',
                ['anything'],
                new class {
                    public function nonTypedMethod($value): void
                    {
                    }
                },
            ],
            [
                'methodAcceptingBoolIntFloat',
                [true, 123, 45.67],
                new class {
                    public function methodAcceptingBoolIntFloat(
                        bool $argument1,
                        int $argument2,
                        float $argument3
                    ): void {
                    }
                },
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function incorrectStrictTypesProvider(): array
    {
        return [
            [
                'emptyMethod',
                ['value'],
                new class {
                    public function emptyMethod(): void
                    {
                    }
                },
            ],
            [
                'methodAcceptingInt',
                ['123'],
                new class {
                    public function methodAcceptingInt(int $value): void
                    {
                    }
                },
            ],
            [
                'methodAcceptingAnInterface',
                [
                    new class {
                    },
                ],
                new class {
                    public function methodAcceptingAnInterface(AnInterface $value): void
                    {
                    }
                },
            ],
            [
                'methodAcceptingBoolInt',
                [123, true],
                new class {
                    public function methodAcceptingBoolInt(
                        bool $argument1,
                        int $argument2
                    ): void {
                    }
                },
            ],
            [
                'methodAcceptingBoolInt',
                [true, 123, 'string'],
                new class {
                    public function methodAcceptingBoolInt(
                        bool $argument1,
                        int $argument2
                    ): void {
                    }
                },
            ],
        ];
    }

    public function testReflectionExceptionOnCheckingStrictTypeOfNonExistingMethod(): void
    {
        $this->expectException(ReflectionException::class);
        $invocation = new Invocation(
            'Foo',
            'nonExistingMethod',
            [],
            'void',
            new class {
            }
        );
        $invocation->checkParameterTypes();
    }

    /**
     * @dataProvider correctStrictTypesProvider
     *
     * @throws ReflectionException
     */
    public function testCorrectStrictTypes(
        string $methodName,
        array $arguments,
        object $object
    ): void {
        $invocation = new Invocation('Foo', $methodName, $arguments, 'void', $object);
        self::assertTrue($invocation->checkParameterTypes());
    }

    /**
     * @dataProvider incorrectStrictTypesProvider
     *
     * @throws ReflectionException
     */
    public function testIncorrectStrictTypes(
        string $methodName,
        array $arguments,
        object $object
    ): void {
        $invocation = new Invocation('Foo', $methodName, $arguments, 'void', $object);
        self::assertFalse($invocation->checkParameterTypes());
    }
}
