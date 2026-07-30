<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoMixedMethodCallerRule\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\TypePerfect\Tests\Rules\NoMixedMethodCallerRule\Source\SomeFinalClass;

final class SkipPHPUnitMock extends TestCase
{
    public function test()
    {
        $someClassMock = $this->createMock(SomeFinalClass::class);

        $someClassMock->expects($this->once())
            ->method('some')
            ->with($this->any())
            ->willReturn(1000);
    }
}
