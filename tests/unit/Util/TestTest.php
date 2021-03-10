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
use function preg_match;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;
use PHPUnit\Metadata\Annotation\DocBlock;
use PHPUnit\TestFixture\DuplicateKeyDataProviderTest;
use PHPUnit\TestFixture\MultipleDataProviderTest;
use PHPUnit\TestFixture\NumericGroupAnnotationTest;
use PHPUnit\TestFixture\RequirementsTest;
use PHPUnit\TestFixture\VariousIterableDataProviderTest;

/**
 * @small
 */
final class TestTest extends TestCase
{
    /**
     * @var string
     */
    private $fileRequirementsTest;

    public function requirementsWithInvalidVersionConstraintsThrowsExceptionProvider(): array
    {
        return [
            ['testVersionConstraintInvalidPhpConstraint'],
            ['testVersionConstraintInvalidPhpUnitConstraint'],
        ];
    }

    /**
     * @testdox Test::getMissingRequirements() for $test
     * @dataProvider missingRequirementsProvider
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Warning
     */
    public function testGetMissingRequirements($test, $result): void
    {
        $this->assertEquals(
            $result,
            Test::getMissingRequirements(RequirementsTest::class, $test)
        );
    }

    public function missingRequirementsProvider(): array
    {
        return [
            ['testOne',            []],
            ['testNine',           [
                'Function testFunc() is required.',
            ]],
            ['testTen',            [
                'PHP extension testExt is required.',
            ]],
            ['testAlwaysSkip',     [
                'PHPUnit >= 1111111 is required.',
            ]],
            ['testAlwaysSkip2',    [
                'PHP >= 9999999 is required.',
            ]],
            ['testAlwaysSkip3',    [
                'Operating system DOESNOTEXIST is required.',
            ]],
            ['testAllPossibleRequirements', [
                'PHP >= 99-dev is required.',
                'PHP extension testExtOne is required.',
                'PHP extension testExt2 is required.',
                'PHP extension testExtThree >= 2.0 is required.',
                'PHPUnit >= 99-dev is required.',
                'Operating system DOESNOTEXIST is required.',
                'Function testFuncOne() is required.',
                'Function testFunc2() is required.',
                'Setting "not_a_setting" is required to be "Off".',
            ]],
            ['testPHPVersionOperatorLessThan', [
                'PHP < 5.4 is required.',
            ]],
            ['testPHPVersionOperatorLessThanEquals', [
                'PHP <= 5.4 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThan', [
                'PHP > 99 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThanEquals', [
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorNoSpace', [
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorEquals', [
                'PHP = 5.4 is required.',
            ]],
            ['testPHPVersionOperatorDoubleEquals', [
                'PHP == 5.4 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThan', [
                'PHPUnit < 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThanEquals', [
                'PHPUnit <= 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThan', [
                'PHPUnit > 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThanEquals', [
                'PHPUnit >= 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorEquals', [
                'PHPUnit = 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorDoubleEquals', [
                'PHPUnit == 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorNoSpace', [
                'PHPUnit >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorLessThan', [
                'PHP extension testExtOne < 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorLessThanEquals', [
                'PHP extension testExtOne <= 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThan', [
                'PHP extension testExtOne > 99 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThanEquals', [
                'PHP extension testExtOne >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorEquals', [
                'PHP extension testExtOne = 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorDoubleEquals', [
                'PHP extension testExtOne == 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorNoSpace', [
                'PHP extension testExtOne >= 99 is required.',
            ]],
            ['testVersionConstraintTildeMajor', [
                'PHP ~1.0 is required.',
                'PHPUnit ~2.0 is required.',
            ]],
            ['testVersionConstraintCaretMajor', [
                'PHP ^1.0 is required.',
                'PHPUnit ^2.0 is required.',
            ]],
        ];
    }

    /**
     * @todo This test does not really test functionality of \PHPUnit\Util\Test
     */
    public function testGetProvidedDataRegEx(): void
    {
        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('class::method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\class::method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider namespace\namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\namespace\class::method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider メソッド', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('メソッド', $matches[1]);
    }

    /**
     * Check if all data providers are being merged.
     */
    public function testMultipleDataProviders(): void
    {
        $dataSets = Test::providedData(MultipleDataProviderTest::class, 'testOne');

        $this->assertCount(9, $dataSets);

        $aCount = 0;
        $bCount = 0;
        $cCount = 0;

        for ($i = 0; $i < 9; $i++) {
            $aCount += $dataSets[$i][0] != null ? 1 : 0;
            $bCount += $dataSets[$i][1] != null ? 1 : 0;
            $cCount += $dataSets[$i][2] != null ? 1 : 0;
        }

        $this->assertEquals(3, $aCount);
        $this->assertEquals(3, $bCount);
        $this->assertEquals(3, $cCount);
    }

    public function testMultipleYieldIteratorDataProviders(): void
    {
        $dataSets = Test::providedData(MultipleDataProviderTest::class, 'testTwo');

        $this->assertCount(9, $dataSets);

        $aCount = 0;
        $bCount = 0;
        $cCount = 0;

        for ($i = 0; $i < 9; $i++) {
            $aCount += $dataSets[$i][0] != null ? 1 : 0;
            $bCount += $dataSets[$i][1] != null ? 1 : 0;
            $cCount += $dataSets[$i][2] != null ? 1 : 0;
        }

        $this->assertEquals(3, $aCount);
        $this->assertEquals(3, $bCount);
        $this->assertEquals(3, $cCount);
    }

    public function testWithVariousIterableDataProvidersFromParent(): void
    {
        $dataSets = Test::providedData(VariousIterableDataProviderTest::class, 'testFromParent');

        $this->assertEquals([
            ['J'],
            ['K'],
            ['L'],
            ['M'],
            ['N'],
            ['O'],
            ['P'],
            ['Q'],
            ['R'],

        ], $dataSets);
    }

    public function testWithVariousIterableDataProvidersInParent(): void
    {
        $dataSets = Test::providedData(VariousIterableDataProviderTest::class, 'testInParent');

        $this->assertEquals([
            ['J'],
            ['K'],
            ['L'],
            ['M'],
            ['N'],
            ['O'],
            ['P'],
            ['Q'],
            ['R'],

        ], $dataSets);
    }

    public function testWithVariousIterableAbstractDataProviders(): void
    {
        $dataSets = Test::providedData(VariousIterableDataProviderTest::class, 'testAbstract');

        $this->assertEquals([
            ['S'],
            ['T'],
            ['U'],
            ['V'],
            ['W'],
            ['X'],
            ['Y'],
            ['Z'],
            ['P'],

        ], $dataSets);
    }

    public function testWithVariousIterableStaticDataProviders(): void
    {
        $dataSets = Test::providedData(VariousIterableDataProviderTest::class, 'testStatic');

        $this->assertEquals([
            ['A'],
            ['B'],
            ['C'],
            ['D'],
            ['E'],
            ['F'],
            ['G'],
            ['H'],
            ['I'],
        ], $dataSets);
    }

    public function testWithVariousIterableNonStaticDataProviders(): void
    {
        $dataSets = Test::providedData(VariousIterableDataProviderTest::class, 'testNonStatic');

        $this->assertEquals([
            ['S'],
            ['T'],
            ['U'],
            ['V'],
            ['W'],
            ['X'],
            ['Y'],
            ['Z'],
            ['P'],
        ], $dataSets);
    }

    public function testWithDuplicateKeyDataProviders(): void
    {
        $this->expectException(InvalidDataProviderException::class);
        $this->expectExceptionMessage('The key "foo" has already been defined by a previous data provider');

        /* @noinspection UnusedFunctionResultInspection */
        Test::providedData(DuplicateKeyDataProviderTest::class, 'test');
    }

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

    /**
     * @testdox Parse @ticket for $class::$method
     * @dataProvider getGroupsProvider
     */
    public function testGetGroupsFromTicketAnnotations(string $class, string $method, array $groups): void
    {
        $this->assertSame($groups, Test::groups($class, $method));
    }

    public function getGroupsProvider(): array
    {
        return [
            [
                NumericGroupAnnotationTest::class,
                'testTicketAnnotationSupportsNumericValue',
                ['t123456', '3502'],
            ],
            [
                NumericGroupAnnotationTest::class,
                'testGroupAnnotationSupportsNumericValue',
                ['t123456', '3502'],
            ],
        ];
    }
}
