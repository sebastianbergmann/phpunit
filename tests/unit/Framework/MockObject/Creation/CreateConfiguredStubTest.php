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
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/test-stub')]
#[Medium]
#[TestDox('createConfiguredStub()')]
final class CreateConfiguredStubTest extends TestCase
{
    public function testCreatesTestStubForInterfaceOrExtendableClassWithReturnValueConfigurationForMultipleMethods(): void
    {
        $double = $this->createConfiguredStub(
            InterfaceWithReturnTypeDeclaration::class,
            [
                'doSomething'     => true,
                'doSomethingElse' => 1,
            ],
        );

        $this->assertTrue($double->doSomething());
        $this->assertSame(1, $double->doSomethingElse(0));
    }
}
