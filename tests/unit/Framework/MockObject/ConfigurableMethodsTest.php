<?php

namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;

class ConfigurableMethodsTest extends TestCase
{
    public function testTwoClassesUsingConfigurableMethodsDontInterfere()
    {
        $configurableMethodsA = [new ConfigurableMethod('foo', SimpleType::fromValue('boolean', false))];
        $configurableMethodsB = [];
        ClassAUsingConfigurableMethods::__phpunit_initConfigurableMethods(...$configurableMethodsA);
        ClassBUsingConfigurableMethods::__phpunit_initConfigurableMethods(...$configurableMethodsB);

        $this->assertSame($configurableMethodsA, ClassAUsingConfigurableMethods::getConfigurableMethods());
        $this->assertSame($configurableMethodsB, ClassBUsingConfigurableMethods::getConfigurableMethods());
    }

}
