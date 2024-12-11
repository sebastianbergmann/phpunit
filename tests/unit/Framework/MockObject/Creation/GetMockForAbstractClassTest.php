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
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Generator\UnknownClassException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AbstractClass;
use ReflectionProperty;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/mock-object')]
#[Medium]
#[RequiresPhpExtension('soap')]
#[TestDox('getMockForAbstractClass()')]
#[IgnorePhpunitDeprecations]
final class GetMockForAbstractClassTest extends TestCase
{
    public function testCreatesMockObjectForAbstractClassAndAllowsConfigurationOfAbstractMethods(): void
    {
        $double = $this->getMockForAbstractClass(AbstractClass::class);

        $double->expects($this->once())->method('doSomethingElse')->willReturn(true);

        $this->assertTrue($double->doSomething());
    }

    public function testCannotCreateMockObjectForAbstractClassThatDoesNotExist(): void
    {
        $this->expectException(UnknownClassException::class);
        $this->expectExceptionMessage('Class "DoesNotExist" does not exist');

        $this->getMockForAbstractClass('DoesNotExist');
    }

    public function testCreatesMockObjectForAbstractClassAndDoesNotAllowConfigurationOfConcreteMethods(): void
    {
        $double = $this->getMockForAbstractClass(AbstractClass::class);

        try {
            $double->expects($this->once())->method('doSomething');
        } catch (MethodCannotBeConfiguredException $e) {
            $this->assertSame('Trying to configure method "doSomething" which cannot be configured because it does not exist, has not been specified, is final, or is static', $e->getMessage());

            return;
        } finally {
            $this->resetMockObjects();
        }

        $this->fail();
    }

    private function resetMockObjects(): void
    {
        (new ReflectionProperty(TestCase::class, 'mockObjects'))->setValue($this, []);
    }
}
