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

use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;
use unit\Framework\MockObject\TestDoubleTestCase;

#[Small]
final class MockObjectTest extends TestDoubleTestCase
{
    #[TestDox('createConfiguredMock() can be used to create a mock object and configure the return value for multiple methods')]
    public function test_createConfiguredMock_works(): void
    {
        $mock = $this->createConfiguredMock(
            InterfaceWithReturnTypeDeclaration::class,
            [
                'doSomething'     => true,
                'doSomethingElse' => 1,
            ],
        );

        $this->assertTrue($mock->doSomething());
        $this->assertSame(1, $mock->doSomethingElse(0));
    }

    /**
     * @psalm-param class-string $type
     */
    protected function createTestDouble(string $type): object
    {
        return $this->createMock($type);
    }

    /**
     * @psalm-param list<class-string> $interfaces
     */
    protected function createTestDoubleForIntersection(array $interfaces): object
    {
        return $this->createMockForIntersectionOfInterfaces($interfaces);
    }
}
