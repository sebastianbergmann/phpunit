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
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExecutionOrderDependency::class)]
class ExecutionOrderDependencyTest extends TestCase
{
    public static function createFromParametersProvider(): array
    {
        return [
            // Dependency on specific class::method target
            ['Class1', 'test1', 'Class1::test1', false],
            ['Class2', 'test2', 'Class2::test2', false],
            ['Class3::method3', null, 'Class3::method3', false],

            // Dependency on whole class
            ['Class4', null, 'Class4::class', true],
            ['Class5', '', 'Class5::class', true],
            ['Class6', 'class', 'Class6::class', true],
            ['Class7::class', null, 'Class7::class', true],
        ];
    }

    public static function createWithCloneOptionProvider(): array
    {
        return [
            'no clone'      => [false, false, false, false],
            'deep clone'    => [true, false, false, true],
            'shallow clone' => [false, true, true, false],
        ];
    }

    public function testCreateDependencyOnClassFromClassNameOnly(): void
    {
        $dependency = new ExecutionOrderDependency('ClassDependency');

        $this->assertTrue($dependency->targetIsClass());
        $this->assertSame('ClassDependency::class', $dependency->getTarget());
        $this->assertSame('ClassDependency', $dependency->getTargetClassName());
    }

    #[DataProvider('createFromParametersProvider')]
    public function testCreateDependencyFromParameters(
        string $className,
        ?string $methodName,
        string $expectedTarget,
        bool $expectedTargetIsClass
    ): void {
        $dependency = new ExecutionOrderDependency($className, $methodName);

        $this->assertSame(
            $expectedTarget,
            $dependency->getTarget(),
            'Incorrect dependency class::method target',
        );

        $this->assertSame(
            $expectedTargetIsClass,
            $dependency->targetIsClass(),
            'Incorrect targetIsClass',
        );
    }

    #[DataProvider('createWithCloneOptionProvider')]
    public function testCreateDependencyWithCloneOption(bool $deepClone, bool $shallowClone, bool $expectedShallowClone, bool $expectedDeepClone): void
    {
        $dependency = new ExecutionOrderDependency('ClassName', 'methodName', $deepClone, $shallowClone);

        $this->assertSame(
            $expectedShallowClone,
            $dependency->shallowClone(),
            'Incorrect shallowClone option',
        );

        $this->assertSame(
            $expectedDeepClone,
            $dependency->deepClone(),
            'Incorrect clone option',
        );
    }

    public function testMergeHandlesEmptyDependencyLists(): void
    {
        $depOne = new ExecutionOrderDependency('classOne');
        $depTwo = new ExecutionOrderDependency('classTwo::methodTwo');

        $this->assertSame(
            [$depOne, $depTwo],
            ExecutionOrderDependency::mergeUnique(
                [],
                [$depOne, $depTwo],
            ),
            'Left side of merge could be empty',
        );

        $this->assertSame(
            [$depOne, $depTwo],
            ExecutionOrderDependency::mergeUnique(
                [$depOne, $depTwo],
                [],
            ),
            'Right side of merge could be empty',
        );
    }

    public function testEmptyClassOrCallable(): void
    {
        $empty = new ExecutionOrderDependency('');
        $this->assertFalse($empty->shallowClone());
        $this->assertFalse($empty->deepClone());
        $this->assertFalse($empty->targetIsClass());
        $this->assertSame('', $empty->getTargetClassName());
    }
}
