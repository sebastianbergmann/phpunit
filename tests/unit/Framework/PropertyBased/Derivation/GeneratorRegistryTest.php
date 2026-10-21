<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Derivation;

use function strtoupper;
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\PropertyBased\ValueGeneratorNotSupportedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Derivation\Money;

#[CoversClass(GeneratorRegistry::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class GeneratorRegistryTest extends TestCase
{
    public function testHasNoGeneratorsWhenItIsEmpty(): void
    {
        $this->assertFalse(GeneratorRegistry::empty()->has(Money::class));
    }

    public function testProvidesTheGeneratorRegisteredForAClassRegardlessOfTheCaseOfItsNameAndALeadingBackslash(): void
    {
        $generator = Generators::constant('money');
        $registry  = GeneratorRegistry::from([Money::class => $generator]);

        $this->assertTrue($registry->has(Money::class));
        $this->assertTrue($registry->has('\\' . strtoupper(Money::class)));
        $this->assertSame($generator, $registry->get('\\' . strtoupper(Money::class)));
    }

    public function testGeneratorsThatWereNotCreatedByPhpunitCannotBeRegistered(): void
    {
        $this->expectException(ValueGeneratorNotSupportedException::class);

        GeneratorRegistry::from(
            [
                Money::class => new class implements ValueGenerator
                {
                    public function map(Closure $mapper): ValueGenerator
                    {
                        return $this;
                    }

                    public function filter(Closure $predicate): ValueGenerator
                    {
                        return $this;
                    }

                    public function flatMap(Closure $mapper): ValueGenerator
                    {
                        return $this;
                    }
                },
            ],
        );
    }
}
