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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;

#[Group('test-doubles')]
#[Small]
final class StubTest extends TestDoubleTestCase
{
    #[TestDox('createConfiguredStub() can be used to create a stub and configure the return value for multiple methods')]
    public function test_createConfiguredStub_works(): void
    {
        $stub = $this->createConfiguredStub(
            InterfaceWithReturnTypeDeclaration::class,
            [
                'doSomething'     => true,
                'doSomethingElse' => 1,
            ],
        );

        $this->assertTrue($stub->doSomething());
        $this->assertSame(1, $stub->doSomethingElse(0));
    }

    /**
     * @psalm-param class-string $type
     */
    protected function createTestDouble(string $type): object
    {
        return $this->createStub($type);
    }

    /**
     * @psalm-param list<class-string> $interfaces
     */
    protected function createTestDoubleForIntersection(array $interfaces): object
    {
        return $this->createStubForIntersectionOfInterfaces($interfaces);
    }
}
