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
use PHPUnit\TestFixture\MockObject\ClassAUsingConfigurableMethods;
use PHPUnit\TestFixture\MockObject\ClassBUsingConfigurableMethods;
use PHPUnit\TestFixture\MockObject\ReinitializeConfigurableMethods;
use SebastianBergmann\Type\SimpleType;

final class ConfigurableMethodsTest extends TestCase
{
    public function testTwoClassesUsingConfigurableMethodsDontInterfere(): void
    {
        $configurableMethodsA = [new ConfigurableMethod('foo', SimpleType::fromValue('boolean', false))];
        $configurableMethodsB = [];
        ClassAUsingConfigurableMethods::__phpunit_initConfigurableMethods(...$configurableMethodsA);
        ClassBUsingConfigurableMethods::__phpunit_initConfigurableMethods(...$configurableMethodsB);

        $this->assertSame($configurableMethodsA, ClassAUsingConfigurableMethods::getConfigurableMethods());
        $this->assertSame($configurableMethodsB, ClassBUsingConfigurableMethods::getConfigurableMethods());
    }

    public function testConfigurableMethodsAreImmutable(): void
    {
        ReinitializeConfigurableMethods::__phpunit_initConfigurableMethods();
        $this->expectException(ConfigurableMethodsAlreadyInitializedException::class);
        ReinitializeConfigurableMethods::__phpunit_initConfigurableMethods();
    }
}
