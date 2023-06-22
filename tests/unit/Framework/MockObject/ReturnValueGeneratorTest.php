<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace unit\Framework\MockObject;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\ReturnValueGenerator;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterface;
use stdClass;

#[CoversClass(ReturnValueGenerator::class)]
#[Group('test-doubles')]
#[Small]
final class ReturnValueGeneratorTest extends TestCase
{
    public function test_Generates_null_for_empty_string(): void
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
        /**
         * @todo Figure out why AnotherInterface is not found by the autoloader
         * when only the tests of this class are run; the interface is found as
         * expected when the entire (unit) test suite is run
         */
        require_once __DIR__ . '/../../../_files/mock-object/AnotherInterface.php';

        $value = $this->generate(AnInterface::class . '&' . AnotherInterface::class);

        $this->assertInstanceOf(Stub::class, $value);
        $this->assertInstanceOf(AnInterface::class, $value);
        $this->assertInstanceOf(AnotherInterface::class, $value);
    }

    public function testGenerates_null_for_union_that_contains_null_and_bool(): void
    {
        $this->assertNull($this->generate('null|bool'));
    }

    public function testGenerates_true_for_union_that_contains_true_and_string(): void
    {
        $this->assertTrue($this->generate('true|string'));
    }

    public function testGenerates_false_for_union_that_contains_bool_and_string(): void
    {
        $this->assertFalse($this->generate('bool|string'));
    }

    #[TestDox('Generates 0.0 for union that contains float')]
    public function test_Generates_00_for_union_that_contains_float_and_string(): void
    {
        $this->assertSame(0.0, $this->generate('float|string'));
    }

    public function test_Generates_0_for_union_that_contains_int_and_string(): void
    {
        $this->assertSame(0, $this->generate('int|string'));
    }

    public function test_Generates_empty_string_for_union_that_contains_string_and_array(): void
    {
        $this->assertSame('', $this->generate('string|array'));
    }

    public function test_Generates_empty_array_for_union_that_contains_array_and_object(): void
    {
        $this->assertSame([], $this->generate('array|stdClass'));
    }

    public function test_Generates_stdClass_object_for_union_that_contains_object_and_unknown_type(): void
    {
        $this->assertInstanceOf(stdClass::class, $this->generate('object|ThisDoesNotExist'));
    }

    private function generate(string $typeDeclaration): mixed
    {
        return (new ReturnValueGenerator)->generate(
            'OriginalClassName',
            'methodName',
            'StubClassName',
            $typeDeclaration,
        );
    }
}
