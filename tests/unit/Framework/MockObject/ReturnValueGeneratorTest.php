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

use function sprintf;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\AnInterfaceForIssue5593;
use PHPUnit\TestFixture\MockObject\AnotherInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterfaceForIssue5593;
use PHPUnit\TestFixture\MockObject\YetAnotherInterface;
use stdClass;

#[CoversClass(ReturnValueGenerator::class)]
#[Group('test-doubles')]
#[Group('test-doubles/test-stub')]
#[Small]
final class ReturnValueGeneratorTest extends TestCase
{
    public static function unionProvider(): array
    {
        return [
            [null, 'null|true|float|int|string|array|object'],
            [null, 'null|false|float|int|string|array|object'],
            [null, 'null|bool|float|int|string|array|object'],
            [true, 'true|float|int|string|array|object'],
            [false, 'false|float|int|string|array|object'],
            [false, 'bool|float|int|string|array|object'],
            [0, 'int|string|array|object'],
            ['', 'string|array|object'],
            [[], 'array|object'],
        ];
    }

    public function test_Generates_null_for_missing_return_type_declaration(): void
    {
        $this->assertNull($this->generate(''));
    }

    public function test_Generates_null_for_null(): void
    {
        $this->assertNull($this->generate('null'));
    }

    public function test_Generates_null_for_mixed(): void
    {
        $this->assertNull($this->generate('mixed'));
    }

    public function test_Generates_null_for_void(): void
    {
        $this->assertNull($this->generate('void'));
    }

    public function test_Generates_true_for_true(): void
    {
        $this->assertTrue($this->generate('true'));
    }

    public function test_Generates_false_for_bool(): void
    {
        $this->assertFalse($this->generate('bool'));
    }

    public function test_Generates_false_for_false(): void
    {
        $this->assertFalse($this->generate('false'));
    }

    #[TestDox('Generates 0.0 for float')]
    public function test_Generates_00_for_float(): void
    {
        $this->assertSame(0.0, $this->generate('float'));
    }

    public function test_Generates_0_for_int(): void
    {
        $this->assertSame(0, $this->generate('int'));
    }

    public function test_Generates_empty_string_for_string(): void
    {
        $this->assertSame('', $this->generate('string'));
    }

    public function test_Generates_empty_array_for_array(): void
    {
        $this->assertSame([], $this->generate('array'));
    }

    public function test_Generates_stdClass_object_for_object(): void
    {
        $this->assertInstanceOf(stdClass::class, $this->generate('object'));
    }

    public function test_Generates_callable_for_callable(): void
    {
        $this->assertIsCallable($this->generate('callable'));
    }

    public function test_Generates_callable_for_Closure(): void
    {
        $this->assertIsCallable($this->generate('Closure'));
    }

    public function test_Generates_Generator_for_Generator(): void
    {
        $this->assertInstanceOf(Generator::class, $this->generate('Generator'));
    }

    public function test_Generates_Generator_for_Traversable(): void
    {
        $this->assertInstanceOf(Generator::class, $this->generate('Traversable'));
    }

    public function test_Generates_Generator_for_iterable(): void
    {
        $this->assertInstanceOf(Generator::class, $this->generate('iterable'));
    }

    public function test_Generates_test_stub_for_class_or_interface_name(): void
    {
        $value = $this->generate(AnInterface::class);

        $this->assertInstanceOf(Stub::class, $value);
        $this->assertInstanceOf(AnInterface::class, $value);
    }

    public function test_Generates_test_stub_for_intersection_of_interfaces(): void
    {
        $value = $this->generate(AnInterface::class . '&' . AnotherInterface::class);

        $this->assertInstanceOf(Stub::class, $value);
        $this->assertInstanceOf(AnInterface::class, $value);
        $this->assertInstanceOf(AnotherInterface::class, $value);
    }

    public function test_Generates_new_instance_of_test_stub_for_static(): void
    {
        $stubClassName = ($this->createStub(AnInterface::class))::class;

        $this->assertInstanceOf($stubClassName, $this->generate('static', $stubClassName));
    }

    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/5593')]
    public function test_Generates_new_instance_of_test_stub_for_static_when_used_recursively(): void
    {
        $a = $this->createStub(AnInterfaceForIssue5593::class);

        $this->assertInstanceOf(AnInterfaceForIssue5593::class, $a);

        $b = $a->doSomething();

        $this->assertInstanceOf(AnotherInterfaceForIssue5593::class, $b);

        $c = $b->doSomethingElse();

        $this->assertInstanceOf(AnotherInterfaceForIssue5593::class, $c);
    }

    #[DataProvider('unionProvider')]
    #[TestDox('Generates $expected for $union')]
    public function test_Generates_return_value_for_union(mixed $expected, string $union): void
    {
        $this->assertSame($expected, $this->generate($union));
    }

    public function test_Generates_stdClass_object_for_union_that_contains_object_and_unknown_type(): void
    {
        $this->assertInstanceOf(stdClass::class, $this->generate('object|ThisDoesNotExist'));
    }

    public function test_Generates_test_stub_for_first_intersection_of_interfaces_found_in_union_of_intersections(): void
    {
        $value = $this->generate(
            sprintf(
                '(%s&%s)|(%s&%s)',
                AnInterface::class,
                AnotherInterface::class,
                AnInterface::class,
                YetAnotherInterface::class,
            ),
        );

        $this->assertInstanceOf(Stub::class, $value);
        $this->assertInstanceOf(AnInterface::class, $value);
        $this->assertInstanceOf(AnotherInterface::class, $value);
    }

    public function test_Generates_test_stub_for_unknown_type(): void
    {
        $this->assertInstanceOf(Stub::class, $this->generate('ThisDoesNotExist'));
    }

    private function generate(string $typeDeclaration, string $stubClassName = 'StubClassName'): mixed
    {
        return (new ReturnValueGenerator)->generate(
            'OriginalClassName',
            'methodName',
            $stubClassName,
            $typeDeclaration,
        );
    }
}
