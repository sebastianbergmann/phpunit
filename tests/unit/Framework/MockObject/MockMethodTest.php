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
use PHPUnit\TestFixture\MockObject\ClassWithoutParentButParentReturnType;
use ReflectionClass;
use RuntimeException;
use SebastianBergmann\Type\UnknownType;

/**
 * @small
 */
final class MockMethodTest extends TestCase
{
    public function testGetNameReturnsMethodName(): void
    {
        $method = new MockMethod(
            'ClassName',
            'methodName',
            false,
            '',
            '',
            '',
            new UnknownType,
            '',
            false,
            false,
            null,
            false
        );
        $this->assertEquals('methodName', $method->getName());
    }

    /**
     * @requires PHP < 7.4
     */
    public function testFailWhenReturnTypeIsParentButThereIsNoParentClass(): void
    {
        $class = new ReflectionClass(ClassWithoutParentButParentReturnType::class);

        $this->expectException(RuntimeException::class);
        MockMethod::fromReflection($class->getMethod('foo'), false, false);
    }
}
