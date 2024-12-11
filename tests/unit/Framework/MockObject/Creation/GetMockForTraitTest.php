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

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Generator\UnknownTraitException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\TraitWithConcreteAndAbstractMethod;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/mock-object')]
#[Medium]
#[TestDox('getMockForTrait()')]
#[IgnorePhpunitDeprecations]
final class GetMockForTraitTest extends TestCase
{
    public function testCreatesMockObjectForTraitAndAllowsConfigurationOfAbstractMethods(): void
    {
        $double = $this->getMockForTrait(TraitWithConcreteAndAbstractMethod::class);

        $double->method('abstractMethod')->willReturn(true);

        $this->assertTrue($double->concreteMethod());
    }

    public function testCannotCreateMockObjectForTraitThatDoesNotExist(): void
    {
        $this->expectException(UnknownTraitException::class);
        $this->expectExceptionMessage('Trait "TraitThatDoesNotExist" does not exist');

        $this->getMockForTrait('TraitThatDoesNotExist');
    }
}
