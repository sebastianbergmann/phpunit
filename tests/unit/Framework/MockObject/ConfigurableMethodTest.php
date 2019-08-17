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
use SebastianBergmann\Type\Type;

final class ConfigurableMethodTest extends TestCase
{
    public function testMethodMayReturnAssignableValue(): void
    {
        $assignableType = $this->createMock(Type::class);
        $assignableType->method('isAssignable')
            ->willReturn(true);
        $configurable = new ConfigurableMethod('foo', $assignableType);
        $this->assertTrue($configurable->mayReturn('everything-is-valid'));
    }

    public function testMethodMayNotReturnUnassignableValue(): void
    {
        $unassignableType = $this->createMock(Type::class);
        $unassignableType->method('isAssignable')
            ->willReturn(false);
        $configurable = new ConfigurableMethod('foo', $unassignableType);
        $this->assertFalse($configurable->mayReturn('everything-is-invalid'));
    }
}
