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

use PHPUnit\Framework\ActualValueIsNotAnObjectException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ComparisonMethodDoesNotAcceptParameterTypeException;
use PHPUnit\Framework\ComparisonMethodDoesNotDeclareBoolReturnTypeException;
use PHPUnit\Framework\ComparisonMethodDoesNotDeclareExactlyOneParameterException;
use PHPUnit\Framework\ComparisonMethodDoesNotDeclareParameterTypeException;
use PHPUnit\Framework\ComparisonMethodDoesNotExistException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ObjectEquals\ValueObject;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatAcceptsTooManyArguments;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatDoesNotAcceptArguments;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatDoesNotDeclareParameterType;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatHasIncompatibleParameterType;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatHasUnionParameterType;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodWithNullableReturnType;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodWithoutReturnType;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodWithUnionReturnType;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodWithVoidReturnType;
use PHPUnit\TestFixture\ObjectEquals\ValueObjectWithoutEqualsMethod;

#[CoversClass(ObjectEquals::class)]
#[CoversClass(ActualValueIsNotAnObjectException::class)]
#[CoversClass(ComparisonMethodDoesNotExistException::class)]
#[CoversClass(ComparisonMethodDoesNotDeclareBoolReturnTypeException::class)]
#[CoversClass(ComparisonMethodDoesNotDeclareExactlyOneParameterException::class)]
#[CoversClass(ComparisonMethodDoesNotDeclareParameterTypeException::class)]
#[CoversClass(ComparisonMethodDoesNotAcceptParameterTypeException::class)]
#[Small]
final class ObjectEqualsTest extends TestCase
{
    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('two objects are equal', (new ObjectEquals(new ValueObject(1)))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new ObjectEquals(new ValueObject(1))));
    }

    public function testAcceptsActualObjectWhenMethodSaysTheyAreEqual(): void
    {
        $this->assertTrue((new ObjectEquals(new ValueObject(1)))->evaluate(new ValueObject(1), '', true));
    }

    public function testRejectsActualValueThatIsNotAnObject(): void
    {
        $this->expectException(ActualValueIsNotAnObjectException::class);
        $this->expectExceptionMessage('Actual value is not an object');

        (new ObjectEquals(new ValueObject(1)))->evaluate(null);
    }

    public function testRejectsActualObjectThatDoesNotHaveTheSpecifiedMethod(): void
    {
        $this->expectException(ComparisonMethodDoesNotExistException::class);
        $this->expectExceptionMessage('Comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithoutEqualsMethod::equals() does not exist.');

        (new ObjectEquals(new ValueObjectWithoutEqualsMethod(1)))->evaluate(new ValueObjectWithoutEqualsMethod(1));
    }

    public function testRejectsActualObjectWhenTheSpecifiedMethodExistsButIsNotDeclaredToReturnBool(): void
    {
        $this->expectException(ComparisonMethodDoesNotDeclareBoolReturnTypeException::class);
        $this->expectExceptionMessage('Comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodWithoutReturnType::equals() does not declare bool return type.');

        (new ObjectEquals(new ValueObjectWithEqualsMethodWithoutReturnType(1)))->evaluate(new ValueObjectWithEqualsMethodWithoutReturnType(1));
    }

    public function testRejectsActualObjectWhenTheSpecifiedMethodExistsButIsDeclaredToReturnUnion(): void
    {
        $this->expectException(ComparisonMethodDoesNotDeclareBoolReturnTypeException::class);
        $this->expectExceptionMessage('Comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodWithUnionReturnType::equals() does not declare bool return type.');

        (new ObjectEquals(new ValueObjectWithEqualsMethodWithUnionReturnType(1)))->evaluate(new ValueObjectWithEqualsMethodWithUnionReturnType(1));
    }

    public function testRejectsActualObjectWhenTheSpecifiedMethodExistsButIsDeclaredVoid(): void
    {
        $this->expectException(ComparisonMethodDoesNotDeclareBoolReturnTypeException::class);
        $this->expectExceptionMessage('Comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodWithVoidReturnType::equals() does not declare bool return type.');

        (new ObjectEquals(new ValueObjectWithEqualsMethodWithVoidReturnType(1)))->evaluate(new ValueObjectWithEqualsMethodWithVoidReturnType(1));
    }

    public function testRejectsActualObjectWhenTheSpecifiedMethodExistsButIsDeclaredNullable(): void
    {
        $this->expectException(ComparisonMethodDoesNotDeclareBoolReturnTypeException::class);
        $this->expectExceptionMessage('Comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodWithNullableReturnType::equals() does not declare bool return type.');

        (new ObjectEquals(new ValueObjectWithEqualsMethodWithNullableReturnType(1)))->evaluate(new ValueObjectWithEqualsMethodWithNullableReturnType(1));
    }

    public function testRejectsActualObjectWhenTheSpecifiedMethodDoesNotAcceptArguments(): void
    {
        $this->expectException(ComparisonMethodDoesNotDeclareExactlyOneParameterException::class);
        $this->expectExceptionMessage('Comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatDoesNotAcceptArguments::equals() does not declare exactly one parameter.');

        (new ObjectEquals(new ValueObjectWithEqualsMethodThatDoesNotAcceptArguments(1)))->evaluate(new ValueObjectWithEqualsMethodThatDoesNotAcceptArguments(1));
    }

    public function testRejectsActualObjectWhenTheSpecifiedMethodAcceptsTooManyArguments(): void
    {
        $this->expectException(ComparisonMethodDoesNotDeclareExactlyOneParameterException::class);
        $this->expectExceptionMessage('Comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatAcceptsTooManyArguments::equals() does not declare exactly one parameter.');

        (new ObjectEquals(new ValueObjectWithEqualsMethodThatAcceptsTooManyArguments(1)))->evaluate(new ValueObjectWithEqualsMethodThatAcceptsTooManyArguments(1));
    }

    public function testRejectsActualObjectWhenTheSpecifiedMethodDoesNotDeclareParameterType(): void
    {
        $this->expectException(ComparisonMethodDoesNotDeclareParameterTypeException::class);
        $this->expectExceptionMessage('Parameter of comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatDoesNotDeclareParameterType::equals() does not have a declared type.');

        (new ObjectEquals(new ValueObjectWithEqualsMethodThatDoesNotDeclareParameterType(1)))->evaluate(new ValueObjectWithEqualsMethodThatDoesNotDeclareParameterType(1));
    }

    public function testRejectsActualObjectWhenTheSpecifiedMethodHasUnionParameterType(): void
    {
        $this->expectException(ComparisonMethodDoesNotDeclareParameterTypeException::class);
        $this->expectExceptionMessage('Parameter of comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatHasUnionParameterType::equals() does not have a declared type.');

        (new ObjectEquals(new ValueObjectWithEqualsMethodThatHasUnionParameterType(1)))->evaluate(new ValueObjectWithEqualsMethodThatHasUnionParameterType(1));
    }

    public function testRejectsActualObjectWhenTheSpecifiedMethodHasIncompatibleParameterType(): void
    {
        $this->expectException(ComparisonMethodDoesNotAcceptParameterTypeException::class);
        $this->expectExceptionMessage('PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatHasIncompatibleParameterType is not an accepted argument type for comparison method PHPUnit\TestFixture\ObjectEquals\ValueObjectWithEqualsMethodThatHasIncompatibleParameterType::equals().');

        (new ObjectEquals(new ValueObjectWithEqualsMethodThatHasIncompatibleParameterType(1)))->evaluate(new ValueObjectWithEqualsMethodThatHasIncompatibleParameterType(1));
    }

    public function testRejectsActualObjectWhenMethodSaysTheyAreNotEqual(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that two objects are equal.');

        (new ObjectEquals(new ValueObject(1)))->evaluate(new ValueObject(2));
    }
}
