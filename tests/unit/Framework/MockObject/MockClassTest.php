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

use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\MockClassWithConfigurableMethods;
use SebastianBergmann\Type\Type;

final class MockClassTest extends TestCase
{
    public function testGenerateClassFromSource(): void
    {
        $mockName = 'PHPUnit\TestFixture\MockObject\MockClassGenerated';

        $file = __DIR__ . '/../../../_files/mock-object/MockClassGenerated.tpl';

        $mockClass = new MockClass(\file_get_contents($file), $mockName, []);
        $mockClass->generate();

        $this->assertTrue(\class_exists($mockName));
    }

    public function testGenerateReturnsNameOfGeneratedClass(): void
    {
        $mockName = 'PHPUnit\TestFixture\MockObject\MockClassGenerated';

        $mockClass = new MockClass('', $mockName, []);

        $this->assertEquals($mockName, $mockClass->generate());
    }

    public function testConfigurableMethodsAreInitalized(): void
    {
        $configurableMethods = [new ConfigurableMethod('foo', Type::fromName('void', false))];
        $mockClass           = new MockClass('', MockClassWithConfigurableMethods::class, $configurableMethods);
        $mockClass->generate();

        $this->assertSame($configurableMethods, MockClassWithConfigurableMethods::getConfigurableMethods());
    }
}
