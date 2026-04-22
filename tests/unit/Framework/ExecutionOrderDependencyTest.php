<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Metadata\Metadata;

#[CoversClass(ExecutionOrderDependency::class)]
#[Small]
class ExecutionOrderDependencyTest extends TestCase
{
    public function testCanBeInvalid(): void
    {
        $dependency = ExecutionOrderDependency::invalid();

        $this->assertFalse($dependency->isValid());
        $this->assertSame('', $dependency->getTarget());
        $this->assertSame('', (string) $dependency);
        $this->assertSame('', $dependency->getTargetClassName());
        $this->assertFalse($dependency->shallowClone());
        $this->assertFalse($dependency->deepClone());
    }

    public function testCanBeDependencyOnClass(): void
    {
        $metadata   = Metadata::dependsOnClass('SomeClass', false, false);
        $dependency = ExecutionOrderDependency::forClass($metadata);

        $this->assertTrue($dependency->isValid());
        $this->assertTrue($dependency->targetIsClass());
        $this->assertSame('SomeClass::class', $dependency->getTarget());
        $this->assertSame('SomeClass::class', (string) $dependency);
        $this->assertSame('SomeClass', $dependency->getTargetClassName());
        $this->assertFalse($dependency->deepClone());
        $this->assertFalse($dependency->shallowClone());
    }

    public function testCanBeDependencyOnClassWithDeepClone(): void
    {
        $metadata   = Metadata::dependsOnClass('SomeClass', true, false);
        $dependency = ExecutionOrderDependency::forClass($metadata);

        $this->assertTrue($dependency->deepClone());
        $this->assertFalse($dependency->shallowClone());
    }

    public function testCanBeDependencyOnClassWithShallowClone(): void
    {
        $metadata   = Metadata::dependsOnClass('SomeClass', false, true);
        $dependency = ExecutionOrderDependency::forClass($metadata);

        $this->assertFalse($dependency->deepClone());
        $this->assertTrue($dependency->shallowClone());
    }

    public function testCanBeDependencyOnMethod(): void
    {
        $metadata   = Metadata::dependsOnMethod('SomeClass', 'someMethod', false, false);
        $dependency = ExecutionOrderDependency::forMethod($metadata);

        $this->assertTrue($dependency->isValid());
        $this->assertFalse($dependency->targetIsClass());
        $this->assertSame('SomeClass::someMethod', $dependency->getTarget());
        $this->assertSame('SomeClass::someMethod', (string) $dependency);
        $this->assertFalse($dependency->deepClone());
        $this->assertFalse($dependency->shallowClone());
    }

    public function testCanBeDependencyOnMethodWithDeepClone(): void
    {
        $metadata   = Metadata::dependsOnMethod('SomeClass', 'someMethod', true, false);
        $dependency = ExecutionOrderDependency::forMethod($metadata);

        $this->assertTrue($dependency->deepClone());
        $this->assertFalse($dependency->shallowClone());
    }

    public function testCanBeDependencyOnMethodShallowClone(): void
    {
        $metadata   = Metadata::dependsOnMethod('SomeClass', 'someMethod', false, true);
        $dependency = ExecutionOrderDependency::forMethod($metadata);

        $this->assertFalse($dependency->deepClone());
        $this->assertTrue($dependency->shallowClone());
    }

    public function testConstructorParsesDoubleColonNotationForClass(): void
    {
        $dependency = new ExecutionOrderDependency('Class::class');

        $this->assertSame('Class::class', $dependency->getTarget());
        $this->assertSame('Class::class', (string) $dependency);
        $this->assertTrue($dependency->targetIsClass());
    }

    public function testConstructorParsesDoubleColonNotationForMethod(): void
    {
        $dependency = new ExecutionOrderDependency('Class::method');

        $this->assertSame('Class::method', $dependency->getTarget());
        $this->assertSame('Class::method', (string) $dependency);
        $this->assertFalse($dependency->targetIsClass());
    }

    public function testInvalidDependenciesAreFiltered(): void
    {
        $valid = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassA', 'methodA', false, false),
        );

        $invalid = ExecutionOrderDependency::invalid();

        $result = ExecutionOrderDependency::filterInvalid([$valid, $invalid]);

        $this->assertCount(1, $result);
        $this->assertSame($valid, $result[0]);
    }

    public function testInvalidDependenciesAreFiltered2(): void
    {
        $result = ExecutionOrderDependency::filterInvalid([
            ExecutionOrderDependency::invalid(),
            ExecutionOrderDependency::invalid(),
        ]);

        $this->assertSame([], $result);
    }

    public function testValidDependenciesAreNotFiltered(): void
    {
        $a = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassA', 'methodA', false, false),
        );

        $b = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassB', 'methodB', false, false),
        );

        $result = ExecutionOrderDependency::filterInvalid([$a, $b]);

        $this->assertCount(2, $result);
        $this->assertSame($a, $result[0]);
        $this->assertSame($b, $result[1]);
    }

    public function testEmptyListsCanBeMerged(): void
    {
        $a = ExecutionOrderDependency::forClass(
            Metadata::dependsOnClass('classOne', false, false),
        );

        $b = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('classTwo', 'methodTwo', false, false),
        );

        $this->assertSame(
            [$a, $b],
            ExecutionOrderDependency::mergeUnique(
                [],
                [$a, $b],
            ),
            'Left side of merge could be empty',
        );

        $this->assertSame(
            [$a, $b],
            ExecutionOrderDependency::mergeUnique(
                [$a, $b],
                [],
            ),
            'Right side of merge could be empty',
        );
    }

    public function testMergingDoesNotAddDuplicates(): void
    {
        $a = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassA', 'methodA', false, false),
        );

        $b = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassB', 'methodB', false, false),
        );

        $c = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassC', 'methodC', false, false),
        );

        $duplicate = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassA', 'methodA', false, false),
        );

        $result = ExecutionOrderDependency::mergeUnique([$a, $b], [$duplicate, $c]);

        $this->assertCount(3, $result);
        $this->assertSame('ClassA::methodA', $result[0]->getTarget());
        $this->assertSame('ClassB::methodB', $result[1]->getTarget());
        $this->assertSame('ClassC::methodC', $result[2]->getTarget());
    }

    public function testDiffReturnsLeftWhenRightIsEmpty(): void
    {
        $a = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassA', 'methodA', false, false),
        );

        $b = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassB', 'methodB', false, false),
        );

        $result = ExecutionOrderDependency::diff([$a, $b], []);

        $this->assertSame([$a, $b], $result);
    }

    public function testDiffReturnsEmptyWhenLeftIsEmpty(): void
    {
        $dependency = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassA', 'methodA', false, false),
        );

        $result = ExecutionOrderDependency::diff([], [$dependency]);

        $this->assertSame([], $result);
    }

    public function testDiffRemovesMatchingDependencies(): void
    {
        $a = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassA', 'methodA', false, false),
        );

        $b = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassB', 'methodB', false, false),
        );

        $c = ExecutionOrderDependency::forMethod(
            Metadata::dependsOnMethod('ClassC', 'methodC', false, false),
        );

        $result = ExecutionOrderDependency::diff([$a, $b, $c], [$b]);

        $this->assertCount(2, $result);
        $this->assertSame($a, $result[0]);
        $this->assertSame($c, $result[1]);
    }
}
