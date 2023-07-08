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
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use ReflectionProperty;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/mock-object')]
#[Medium]
#[TestDox('createPartialMock()')]
final class CreatePartialMockTest extends TestCase
{
    public function testCreatesPartialMockObjectForExtendableClass(): void
    {
        $mock = $this->createPartialMock(ExtendableClass::class, ['doSomethingElse']);

        $mock->expects($this->once())->method('doSomethingElse')->willReturn(true);

        $this->assertTrue($mock->doSomething());
    }

    public function testMethodOfPartialMockThatIsNotConfigurableCannotBeConfigured(): void
    {
        $mock = $this->createPartialMock(ExtendableClass::class, ['doSomethingElse']);

        try {
            $mock->expects($this->once())->method('doSomething')->willReturn(true);
        } catch (MethodCannotBeConfiguredException $e) {
            $this->assertSame('Trying to configure method "doSomething" which cannot be configured because it does not exist, has not been specified, is final, or is static', $e->getMessage());

            return;
        } finally {
            $this->resetMockObjects();
        }

        $this->fail();
    }

    public function testMethodOfPartialMockThatDoesNotExistCannotBeConfigured(): void
    {
        $this->expectException(CannotUseOnlyMethodsException::class);
        $this->expectExceptionMessage('Trying to configure method "doesNotExist" with onlyMethods(), but it does not exist in class "PHPUnit\TestFixture\MockObject\ExtendableClass"');

        $this->createPartialMock(ExtendableClass::class, ['doesNotExist']);
    }

    private function resetMockObjects(): void
    {
        (new ReflectionProperty(TestCase::class, 'mockObjects'))->setValue($this, []);
    }
}
