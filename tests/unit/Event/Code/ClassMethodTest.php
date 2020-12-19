<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Comparator\Registered
 */
final class ClassMethodTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $className  = self::class;
        $methodName = 'foo';

        $classMethod = new ClassMethod(
            $className,
            $methodName
        );

        $this->assertSame($className, $classMethod->className());
        $this->assertSame($methodName, $classMethod->methodName());
    }
}
