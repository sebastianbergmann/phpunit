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
    public function testMethodMayReturnValueThatCanBeAssigned(): void
    {
        $type = $this->createMock(Type::class);

        $type->method('isAssignable')
             ->willReturn(true);

        $method = new ConfigurableMethod('foo', $type);

        $this->assertTrue($method->mayReturn('everything-is-valid'));
    }

    public function testMethodMayNotReturnValueThatCannotBeAssigned(): void
    {
        $type = $this->createMock(Type::class);

        $type->method('isAssignable')
             ->willReturn(false);

        $method = new ConfigurableMethod('foo', $type);

        $this->assertFalse($method->mayReturn('everything-is-invalid'));
    }
}
