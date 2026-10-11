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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\UseGenerators;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress;

#[UseGenerators(EmailAddressGenerators::class)]
final class UseGeneratorsTest extends TestCase
{
    #[Property]
    public function testUsesTheGeneratorsOfTheClassThatIsUsedForTheTestCaseClass(EmailAddress $emailAddress): void
    {
    }

    #[Property]
    #[UseGenerators(OtherEmailAddressGenerators::class)]
    public function testUsesTheGeneratorsOfTheClassThatIsUsedForTheProperty(EmailAddress $emailAddress): void
    {
    }

    #[Property]
    #[UseGenerators(EmailAddressGenerators::class)]
    #[UseGenerators(OtherEmailAddressGenerators::class)]
    public function testUsesTwoClassesThatRegisterAGeneratorForTheSameClass(EmailAddress $emailAddress): void
    {
    }

    #[Property]
    #[UseGenerators('PHPUnit\TestFixture\PropertyBased\Properties\DoesNotExist')]
    public function testUsesAClassThatDoesNotExist(int $value): void
    {
    }

    #[Property]
    #[UseGenerators(EmailAddress::class)]
    public function testUsesAnInterface(int $value): void
    {
    }

    #[Property]
    #[UseGenerators(GeneratorForEmailAddresses::class)]
    public function testUsesATrait(int $value): void
    {
    }

    #[Property]
    #[UseGenerators(ClassWithoutGeneratorFor::class)]
    public function testUsesAClassThatDoesNotRegisterAGenerator(int $value): void
    {
    }
}
