<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Framework\ExecutionOrderDependency
 */
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
            // Cloning option values
            ['clone', false, true],
            ['!clone', false, false],
            ['shallowClone', true, false],
            ['!shallowClone', false, false],
        ];
    }

    public function testCreateDependencyOnClassFromClassNameOnly(): void
    {
        $dependency = new ExecutionOrderDependency('ClassDependency');

        $this->assertTrue($dependency->targetIsClass());
        $this->assertSame('ClassDependency::class', $dependency->getTarget());
        $this->assertSame('ClassDependency', $dependency->getTargetClassName());
    }

    public function testDependsAnnotationRequireATargetToBeValid(): void
    {
        $dependency = ExecutionOrderDependency::createFromDependsAnnotation('SomeClass', '');

        $this->assertFalse($dependency->isValid());
        $this->assertSame('', $dependency->getTarget());
    }

    /**
     * @testdox Create valid dependency from [$className, $methodName]
     * @dataProvider createFromParametersProvider
     */
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
            'Incorrect dependency class::method target'
        );

        $this->assertSame(
            $expectedTargetIsClass,
            $dependency->targetIsClass(),
            'Incorrect targetIsClass'
        );
    }

    /**
     * @testdox Create valid dependency with clone option $option
     * @dataProvider createWithCloneOptionProvider
     */
    public function testCreateDependencyWithCloneOption(
        ?string $option,
        bool $expectedShallowClone,
        bool $expectedDeepClone
    ): void {
        $dependency = new ExecutionOrderDependency('ClassName', 'methodName', $option);

        $this->assertSame(
            $expectedShallowClone,
            $dependency->useShallowClone(),
            'Incorrect shallowClone option'
        );

        $this->assertSame(
            $expectedDeepClone,
            $dependency->useDeepClone(),
            'Incorrect clone option'
        );
    }

    public function testCreateDependencyFromAnnotation(): void
    {
        $dependency = ExecutionOrderDependency::createFromDependsAnnotation('ClassOne', 'ClassOne::methodOne');

        $this->assertSame('ClassOne::methodOne', $dependency->getTarget());
    }

    public function testCreateDependencyFromAnnotationWithCloneOption(): void
    {
        $dependency = ExecutionOrderDependency::createFromDependsAnnotation('ClassOne', 'clone methodOne');

        $this->assertSame('ClassOne::methodOne', $dependency->getTarget());
        $this->assertTrue($dependency->useDeepClone());
    }

    public function testMergeHandlesEmptyDependencyLists(): void
    {
        $depOne = new ExecutionOrderDependency('classOne');
        $depTwo = new ExecutionOrderDependency('classTwo::methodTwo');

        $this->assertSame(
            [$depOne, $depTwo],
            ExecutionOrderDependency::mergeUnique(
                [],
                [$depOne, $depTwo]
            ),
            'Left side of merge could be empty'
        );

        $this->assertSame(
            [$depOne, $depTwo],
            ExecutionOrderDependency::mergeUnique(
                [$depOne, $depTwo],
                []
            ),
            'Right side of merge could be empty'
        );
    }

    public function testMergeUniqueDependencies(): void
    {
        $depOne   = new ExecutionOrderDependency('classOne');
        $depTwo   = new ExecutionOrderDependency('classTwo::methodTwo');
        $depThree = ExecutionOrderDependency::createFromDependsAnnotation('classThree', 'clone methodThree');

        $this->assertSame(
            [$depOne, $depTwo, $depThree],
            ExecutionOrderDependency::mergeUnique(
                [$depOne, $depTwo],
                [$depTwo, $depThree]
            )
        );
    }

    public function testDiffDependencies(): void
    {
        $depOne        = new ExecutionOrderDependency('classOne');
        $depTwo        = new ExecutionOrderDependency('classTwo::methodTwo');
        $depThree      = new ExecutionOrderDependency('classThree::methodThree');
        $depThreeClone = ExecutionOrderDependency::createFromDependsAnnotation('classThree', 'clone methodThree');
        $depFour       = new ExecutionOrderDependency('classFour::methodFour');

        $this->assertSame(
            [],
            ExecutionOrderDependency::diff(
                [$depOne, $depTwo],
                [$depOne, $depTwo, $depThree, $depFour]
            )
        );

        $this->assertSame(
            [$depOne, $depTwo],
            ExecutionOrderDependency::diff(
                [$depOne, $depTwo],
                [$depThree, $depFour]
            )
        );

        $this->assertSame(
            [$depOne, $depFour],
            ExecutionOrderDependency::diff(
                [$depOne, $depTwo, $depThree, $depFour],
                [$depTwo, $depThreeClone]
            )
        );

        $this->assertSame(
            [$depOne, $depTwo, $depThree, $depFour],
            ExecutionOrderDependency::diff(
                [$depOne, $depTwo, $depThree, $depFour],
                []
            )
        );

        $this->assertSame([], ExecutionOrderDependency::diff([], []));
    }
}
