<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Metadata\Annotation\DependencyTest;

/**
 * @small
 */
final class TestTest extends TestCase
{
    public function testParseDependsAnnotation(): void
    {
        $this->assertEquals(
            [
                new ExecutionOrderDependency(DependencyTest::class, 'Foo'),
                new ExecutionOrderDependency(DependencyTest::class, 'ほげ'),
                new ExecutionOrderDependency('AnotherClass::Foo'),
            ],
            Test::getDependencies(
                DependencyTest::class,
                'testOne'
            )
        );
    }

    public function testParseAnnotationThatIsOnlyOneLine(): void
    {
        $this->assertEquals(
            [new ExecutionOrderDependency(DependencyTest::class, 'Bar')],
            Test::getDependencies(
                DependencyTest::class,
                'testTwo'
            )
        );
    }
}
