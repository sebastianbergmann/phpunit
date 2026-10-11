<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Properties;

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\ForAllExternal;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\RequireClassification;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\TestWithJson;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Derivation\Customer;
use PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress;
use PHPUnit\TestFixture\PropertyBased\Derivation\Money;

final class ValidPropertiesTest extends TestCase
{
    use GeneratorForEmailAddresses;

    /**
     * @return ValueGenerator<int>
     */
    public static function smallIntegers(): ValueGenerator
    {
        return Generators::integers(0, 9);
    }

    /**
     * @param non-empty-string $name
     */
    #[Property(examples: 50)]
    public function testWithParameters(#[ForAll('smallIntegers')] int $small, string $name, Money $money): void
    {
    }

    #[Property]
    public function testWithRegisteredGenerator(Customer $customer, EmailAddress $emailAddress): void
    {
    }

    #[Property]
    #[TestWith([1, 'one'])]
    #[TestWith(['name' => 'two', 'small' => 2], 'named arguments')]
    #[TestWith([3, 'name' => 'three'])]
    #[TestWithJson('[4, "four"]')]
    public function testWithExplicitExamples(#[ForAll('smallIntegers')] int $small, string $name): void
    {
    }

    #[Property]
    #[RequireClassification('negative', 10)]
    #[RequireClassification('zero', 100)]
    public function testWithRequiredClassifications(int $value): void
    {
    }

    #[Property]
    public function testWithGeneratorOfAnotherClass(#[ForAllExternal(EmailAddressGenerators::class, 'emailAddresses')] EmailAddress $emailAddress): void
    {
    }

    #[Property]
    public function testWithoutParameters(): void
    {
    }
}
