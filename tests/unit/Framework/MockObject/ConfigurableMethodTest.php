<?php

namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;

class ConfigurableMethodTest extends TestCase
{

    public function testMethodMayReturnAssignableValue()
    {
        $assignableType = $this->createMock(Type::class);
        $assignableType->method('isAssignable')
            ->willReturn(true);
        $configurable = new ConfigurableMethod('foo', $assignableType);
        $this->assertTrue($configurable->mayReturn('everything-is-valid'));
    }

    public function testMethodMayNotReturnUnassignableValue()
    {
        $unassignableType = $this->createMock(Type::class);
        $unassignableType->method('isAssignable')
            ->willReturn(false);
        $configurable = new ConfigurableMethod('foo', $unassignableType);
        $this->assertFalse($configurable->mayReturn('everything-is-invalid'));
    }

}
