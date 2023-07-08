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
#[Group('test-doubles/mock-object')]
#[Medium]
#[TestDox('createConfiguredMock()')]
final class CreateConfiguredMockTest extends TestCase
{
    public function testCreatesMockObjectForInterfaceOrExtendableClassWithReturnValueConfigurationForMultipleMethods(): void
    {
        $stub = $this->createConfiguredMock(
            InterfaceWithReturnTypeDeclaration::class,
            [
                'doSomething'     => true,
                'doSomethingElse' => 1,
            ],
        );

        $this->assertTrue($stub->doSomething());
        $this->assertSame(1, $stub->doSomethingElse(0));
    }
}
