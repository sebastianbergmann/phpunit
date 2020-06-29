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
use PHPUnit\TestFixture\Mockable;
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
                'methodWithInt',
                [123],
                new class {
                    public function methodWithInt(int $argument1): void
                    {
                    }
                },
            ],
            [
                'methodWithAnInterface',
                [
                    new class implements AnInterface {
                        public function doSomething(): void
                        {
                        }
                    },
                ],
                new class {
                    public function methodWithAnInterface(AnInterface $argument1): void
                    {
                    }
                },
            ],
            [
                'methodWithMockable',
                [new Mockable()],
                new class {
                    public function methodWithMockable(Mockable $argument1): void
                    {
                    }
                },
            ],
            [
                'methodWithDefaultValue',
                [],
                new class {
                    public function methodWithDefaultValue(bool $argument1 = true): void
                    {
                    }
                },
            ],
            [
                'nonTypedMethod',
                ['anything', null],
                new class {
                    public function nonTypedMethod($argument1, $argument2): void
                    {
                    }
                },
            ],
            [
                'methodWithBoolIntFloat',
                [true, 123, 45.67],
                new class {
                    public function methodWithBoolIntFloat(
                        bool $argument1,
                        int $argument2,
                        float $argument3
                    ): void {
                    }
                },
            ],
            [
                'methodWithNullableString',
                [null],
                new class {
                    public function methodWithNullableString(?string $argument1): void
                    {
                    }
                },
            ],
            [
                'methodWithVariadicString',
                ['a', 'b', 'c'],
                new class {
                    public function methodWithVariadicString(string ...$argument1): void
                    {
                    }
                },
            ],
            [
                'methodWithVariadicString',
                [],
                new class {
                    public function methodWithVariadicString(string ...$argument1): void
                    {
                    }
                },
            ],
            [
                'methodWithIntVariadicNullableString',
                [3, 'a', null, 'b'],
                new class {
                    public function methodWithIntVariadicNullableString(
                        int $argument1,
                        ?string ...$argument2
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
                'methodWithInt',
                ['123'],
                new class {
                    public function methodWithInt(int $value): void
                    {
                    }
                },
            ],
            [
                'methodWithAnInterface',
                [
                    new class {
                    },
                ],
                new class {
                    public function methodWithAnInterface(AnInterface $value): void
                    {
                    }
                },
            ],
            [
                'methodWithBoolInt',
                [123, true],
                new class {
                    public function methodWithBoolInt(
                        bool $argument1,
                        int $argument2
                    ): void {
                    }
                },
            ],
            [
                'methodWithBoolInt',
                [true, 123, 'string'],
                new class {
                    public function methodWithBoolInt(
                        bool $argument1,
                        int $argument2
                    ): void {
                    }
                },
            ],
            [
                'methodWithNullableString',
                [0],
                new class {
                    public function methodWithNullableString(?string $argument1): void
                    {
                    }
                },
            ],
            [
                'methodWithVariadicString',
                ['a', false, 'b'],
                new class {
                    public function methodWithVariadicString(string...$argument1): void
                    {
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
