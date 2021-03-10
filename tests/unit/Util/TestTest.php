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

use function get_class;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class TestTest extends TestCase
{
    public function testParseDependsAnnotation(): void
    {
        $this->assertEquals(
            [
                new ExecutionOrderDependency(get_class($this), 'Foo'),
                new ExecutionOrderDependency(get_class($this), 'ほげ'),
                new ExecutionOrderDependency('AnotherClass::Foo'),
            ],
            Test::getDependencies(get_class($this), 'methodForTestParseAnnotation')
        );
    }

    /**
     * @depends Foo
     * @depends ほげ
     * @depends AnotherClass::Foo
     *
     * @todo Remove fixture from test class
     */
    public function methodForTestParseAnnotation(): void
    {
    }

    public function testParseAnnotationThatIsOnlyOneLine(): void
    {
        $this->assertEquals(
            [new ExecutionOrderDependency(get_class($this), 'Bar')],
            Test::getDependencies(get_class($this), 'methodForTestParseAnnotationThatIsOnlyOneLine')
        );
    }

    /** @depends Bar */
    public function methodForTestParseAnnotationThatIsOnlyOneLine(): void
    {
        // TODO Remove fixture from test class
    }
}
