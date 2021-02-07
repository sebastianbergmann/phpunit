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

use function array_merge;
use function get_class;
use function preg_match;
use function range;
use function realpath;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;
use PHPUnit\TestFixture\CoverageClassNothingTest;
use PHPUnit\TestFixture\CoverageClassTest;
use PHPUnit\TestFixture\CoverageClassWithoutAnnotationsTest;
use PHPUnit\TestFixture\CoverageCoversOverridesCoversNothingTest;
use PHPUnit\TestFixture\CoverageFunctionParenthesesTest;
use PHPUnit\TestFixture\CoverageFunctionParenthesesWhitespaceTest;
use PHPUnit\TestFixture\CoverageFunctionTest;
use PHPUnit\TestFixture\CoverageMethodNothingCoversMethod;
use PHPUnit\TestFixture\CoverageMethodNothingTest;
use PHPUnit\TestFixture\CoverageMethodOneLineAnnotationTest;
use PHPUnit\TestFixture\CoverageMethodParenthesesTest;
use PHPUnit\TestFixture\CoverageMethodParenthesesWhitespaceTest;
use PHPUnit\TestFixture\CoverageMethodTest;
use PHPUnit\TestFixture\CoverageNamespacedFunctionTest;
use PHPUnit\TestFixture\CoverageNoneTest;
use PHPUnit\TestFixture\DuplicateKeyDataProviderTest;
use PHPUnit\TestFixture\MultipleDataProviderTest;
use PHPUnit\TestFixture\NamespaceCoverageClassTest;
use PHPUnit\TestFixture\NamespaceCoverageCoversClassPublicTest;
use PHPUnit\TestFixture\NamespaceCoverageCoversClassTest;
use PHPUnit\TestFixture\NamespaceCoverageMethodTest;
use PHPUnit\TestFixture\NumericGroupAnnotationTest;
use PHPUnit\TestFixture\ParseTestMethodAnnotationsMock;
use PHPUnit\TestFixture\RequirementsTest;
use PHPUnit\TestFixture\Test3194;
use PHPUnit\TestFixture\VariousDocblockDefinedDataProvider;
use PHPUnit\TestFixture\VariousIterableDataProviderTest;
use PHPUnit\Util\Metadata\Annotation\DocBlock;
use ReflectionClass;
use ReflectionMethod;

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
                '__OFFSET_LINE=71',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Function testFunc is required.',
            ]],
            ['testTen',            [
                '__OFFSET_LINE=87',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExt is required.',
            ]],
            ['testAlwaysSkip',     [
                '__OFFSET_LINE=145',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit >= 1111111 is required.',
            ]],
            ['testAlwaysSkip2',    [
                '__OFFSET_LINE=152',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 9999999 is required.',
            ]],
            ['testAlwaysSkip3',    [
                '__OFFSET_LINE=159',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Operating system matching /DOESNOTEXIST/i is required.',
            ]],
            ['testAllPossibleRequirements', [
                '__OFFSET_LINE=102',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 99-dev is required.',
                'PHPUnit >= 99-dev is required.',
                'Operating system matching /DOESNOTEXIST/i is required.',
                'Function testFuncOne is required.',
                'Function testFunc2 is required.',
                'Setting "not_a_setting" must be "Off".',
                'Extension testExtOne is required.',
                'Extension testExt2 is required.',
                'Extension testExtThree >= 2.0 is required.',
            ]],
            ['testPHPVersionOperatorLessThan', [
                '__OFFSET_LINE=189',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP < 5.4 is required.',
            ]],
            ['testPHPVersionOperatorLessThanEquals', [
                '__OFFSET_LINE=197',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP <= 5.4 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThan', [
                '__OFFSET_LINE=205',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP > 99 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThanEquals', [
                '__OFFSET_LINE=213',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorNoSpace', [
                '__OFFSET_LINE=253',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorEquals', [
                '__OFFSET_LINE=221',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP = 5.4 is required.',
            ]],
            ['testPHPVersionOperatorDoubleEquals', [
                '__OFFSET_LINE=229',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP == 5.4 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThan', [
                '__OFFSET_LINE=261',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit < 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThanEquals', [
                '__OFFSET_LINE=269',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit <= 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThan', [
                '__OFFSET_LINE=277',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit > 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThanEquals', [
                '__OFFSET_LINE=285',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit >= 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorEquals', [
                '__OFFSET_LINE=293',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit = 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorDoubleEquals', [
                '__OFFSET_LINE=301',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit == 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorNoSpace', [
                '__OFFSET_LINE=325',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorLessThan', [
                '__OFFSET_LINE=332',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne < 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorLessThanEquals', [
                '__OFFSET_LINE=339',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne <= 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThan', [
                '__OFFSET_LINE=346',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne > 99 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThanEquals', [
                '__OFFSET_LINE=353',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorEquals', [
                '__OFFSET_LINE=360',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne = 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorDoubleEquals', [
                '__OFFSET_LINE=367',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne == 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorNoSpace', [
                '__OFFSET_LINE=388',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne >= 99 is required.',
            ]],
            ['testVersionConstraintTildeMajor', [
                '__OFFSET_LINE=395',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP version does not match the required constraint ~1.0.',
                'PHPUnit version does not match the required constraint ~2.0.',
            ]],
            ['testVersionConstraintCaretMajor', [
                '__OFFSET_LINE=403',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP version does not match the required constraint ^1.0.',
                'PHPUnit version does not match the required constraint ^2.0.',
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
        $dataSets = Test::getProvidedData(MultipleDataProviderTest::class, 'testOne');

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
        $dataSets = Test::getProvidedData(MultipleDataProviderTest::class, 'testTwo');

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
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testFromParent');

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
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testInParent');

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
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testAbstract');

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
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testStatic');

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
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testNonStatic');

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
        $this->expectExceptionMessage('The key "foo" has already been defined in the data provider "dataProvider".');

        Test::getProvidedData(DuplicateKeyDataProviderTest::class, 'test');
    }

    public function testTestWithEmptyAnnotation(): void
    {
        $result = DocBlock::ofMethod(
            new ReflectionMethod(
                VariousDocblockDefinedDataProvider::class,
                'anotherAnnotation'
            ),
            VariousDocblockDefinedDataProvider::class
        )->getProvidedData();

        $this->assertNull($result);
    }

    public function testTestWithSimpleCase(): void
    {
        $result = DocBlock::ofMethod(
            new ReflectionMethod(
                VariousDocblockDefinedDataProvider::class,
                'testWith1'
            ),
            VariousDocblockDefinedDataProvider::class
        )->getProvidedData();

        $this->assertEquals([[1]], $result);
    }

    public function testTestWithMultiLineMultiParameterCase(): void
    {
        $result = DocBlock::ofMethod(
            new ReflectionMethod(
                VariousDocblockDefinedDataProvider::class,
                'testWith1234'
            ),
            VariousDocblockDefinedDataProvider::class
        )->getProvidedData();

        $this->assertEquals([[1, 2], [3, 4]], $result);
    }

    public function testTestWithVariousTypes(): void
    {
        $result = DocBlock::ofMethod(
            new ReflectionMethod(
                VariousDocblockDefinedDataProvider::class,
                'testWithABTrueNull'
            ),
            VariousDocblockDefinedDataProvider::class
        )->getProvidedData();

        $this->assertEquals([['ab'], [true], [null]], $result);
    }

    public function testTestWithAnnotationAfter(): void
    {
        $result = DocBlock::ofMethod(
            new ReflectionMethod(
                VariousDocblockDefinedDataProvider::class,
                'testWith12AndAnotherAnnotation'
            ),
            VariousDocblockDefinedDataProvider::class
        )->getProvidedData();

        $this->assertEquals([[1], [2]], $result);
    }

    public function testTestWithSimpleTextAfter(): void
    {
        $result = DocBlock::ofMethod(
            new ReflectionMethod(
                VariousDocblockDefinedDataProvider::class,
                'testWith12AndBlahBlah'
            ),
            VariousDocblockDefinedDataProvider::class
        )->getProvidedData();

        $this->assertEquals([[1], [2]], $result);
    }

    public function testTestWithCharacterEscape(): void
    {
        $result = DocBlock::ofMethod(
            new ReflectionMethod(
                VariousDocblockDefinedDataProvider::class,
                'testWithEscapedString'
            ),
            VariousDocblockDefinedDataProvider::class
        )->getProvidedData();

        $this->assertEquals([['"', '"']], $result);
    }

    public function testTestWithThrowsProperExceptionIfDatasetCannotBeParsed(): void
    {
        $docBlock = DocBlock::ofMethod(
            new ReflectionMethod(
                VariousDocblockDefinedDataProvider::class,
                'testWithMalformedValue'
            ),
            VariousDocblockDefinedDataProvider::class
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^The data set for the @testWith annotation cannot be parsed:/');

        $docBlock->getProvidedData();
    }

    public function testTestWithThrowsProperExceptionIfMultiLineDatasetCannotBeParsed(): void
    {
        $docBlock = DocBlock::ofMethod(
            new ReflectionMethod(
                VariousDocblockDefinedDataProvider::class,
                'testWithWellFormedAndMalformedValue'
            ),
            VariousDocblockDefinedDataProvider::class
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^The data set for the @testWith annotation cannot be parsed:/');

        $docBlock->getProvidedData();
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
     * @dataProvider getLinesToBeCoveredProvider
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws CodeCoverageException
     */
    public function testGetLinesToBeCovered($test, $lines): void
    {
        switch ($test) {
            case CoverageMethodNothingCoversMethod::class:
            case CoverageClassNothingTest::class:
            case CoverageMethodNothingTest::class:
                $expected = false;

                break;

            case CoverageCoversOverridesCoversNothingTest::class:
                $expected = [TEST_FILES_PATH . 'CoveredClass.php' => $lines];

                break;

            case CoverageNoneTest::class:
                $expected = [];

                break;

            case CoverageFunctionTest::class:
                $expected = [
                    TEST_FILES_PATH . 'CoveredFunction.php' => $lines,
                ];

                break;

            case NamespaceCoverageClassTest::class:
            case NamespaceCoverageMethodTest::class:
            case NamespaceCoverageCoversClassTest::class:
            case NamespaceCoverageCoversClassPublicTest::class:
                $expected = [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => $lines,
                ];

                break;

            default:
                $expected = [TEST_FILES_PATH . 'CoveredClass.php' => $lines];
        }

        $this->assertEqualsCanonicalizing(
            $expected,
            Test::getLinesToBeCovered(
                $test,
                'testSomething'
            )
        );
    }

    public function testFunctionParenthesesAreAllowed(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => range(10, 12)],
            Test::getLinesToBeCovered(
                CoverageFunctionParenthesesTest::class,
                'testSomething'
            )
        );
    }

    public function testFunctionParenthesesAreAllowedWithWhitespace(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => range(10, 12)],
            Test::getLinesToBeCovered(
                CoverageFunctionParenthesesWhitespaceTest::class,
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowed(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => range(31, 35)],
            Test::getLinesToBeCovered(
                CoverageMethodParenthesesTest::class,
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowedWithWhitespace(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => range(31, 35)],
            Test::getLinesToBeCovered(
                CoverageMethodParenthesesWhitespaceTest::class,
                'testSomething'
            )
        );
    }

    public function testNamespacedFunctionCanBeCoveredOrUsed(): void
    {
        $this->assertEquals(
            [
                TEST_FILES_PATH . 'NamespaceCoveredFunction.php' => range(12, 15),
            ],
            Test::getLinesToBeCovered(
                CoverageNamespacedFunctionTest::class,
                'testFunc'
            )
        );
    }

    public function getLinesToBeCoveredProvider(): array
    {
        return [
            [
                CoverageNoneTest::class,
                [],
            ],
            [
                CoverageClassTest::class,
                range(29, 46),
            ],
            [
                CoverageMethodTest::class,
                range(31, 35),
            ],
            [
                CoverageMethodOneLineAnnotationTest::class,
                range(31, 35),
            ],
            [
                CoverageFunctionTest::class,
                range(10, 12),
            ],
            [
                NamespaceCoverageClassTest::class,
                range(29, 46),
            ],
            [
                NamespaceCoverageMethodTest::class,
                range(31, 35),
            ],
            [
                NamespaceCoverageCoversClassTest::class,
                array_merge(range(43, 45), range(37, 41), range(31, 35), range(24, 26), range(19, 22), range(14, 17)),
            ],
            [
                NamespaceCoverageCoversClassPublicTest::class,
                range(31, 35),
            ],
            [
                CoverageClassNothingTest::class,
                false,
            ],
            [
                CoverageMethodNothingTest::class,
                false,
            ],
            [
                CoverageCoversOverridesCoversNothingTest::class,
                range(31, 35),
            ],
            [
                CoverageMethodNothingCoversMethod::class,
                false,
            ],
        ];
    }

    public function testParseTestMethodAnnotationsIncorporatesTraits(): void
    {
        $result = Test::parseTestMethodAnnotations(ParseTestMethodAnnotationsMock::class);

        $this->assertArrayHasKey('class', $result);
        $this->assertArrayHasKey('method', $result);
        $this->assertArrayHasKey('theClassAnnotation', $result['class']);
        $this->assertArrayHasKey('theTraitAnnotation', $result['class']);
    }

    public function testCoversAnnotationIncludesTraitsUsedByClass(): void
    {
        $this->assertSame(
            [
                TEST_FILES_PATH . '3194.php' => array_merge(range(14, 20), range(22, 30)),
            ],
            Test::getLinesToBeCovered(
                Test3194::class,
                'testOne'
            )
        );
    }

    /**
     * @dataProvider canSkipCoverageProvider
     */
    public function testCanSkipCoverage($testCase, $expectedCanSkip): void
    {
        $test             = new $testCase('testSomething');
        $coverageRequired = Test::shouldCodeCoverageBeCollectedFor(get_class($test), $test->getName(false));
        $canSkipCoverage  = !$coverageRequired;

        $this->assertEquals($expectedCanSkip, $canSkipCoverage);
    }

    public function canSkipCoverageProvider(): array
    {
        return [
            [CoverageClassTest::class, false],
            [CoverageClassWithoutAnnotationsTest::class, false],
            [CoverageCoversOverridesCoversNothingTest::class, false],
            [CoverageClassNothingTest::class, true],
            [CoverageMethodNothingTest::class, true],
        ];
    }

    /**
     * @testdox Parse @ticket for $class::$method
     * @dataProvider getGroupsProvider
     */
    public function testGetGroupsFromTicketAnnotations(string $class, string $method, array $groups): void
    {
        $this->assertSame($groups, Test::getGroups($class, $method));
    }

    public function getGroupsProvider(): array
    {
        return [
            [
                NumericGroupAnnotationTest::class,
                '',
                ['t123456'],
            ],
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

    private function getRequirementsTestClassFile(): string
    {
        if (!$this->fileRequirementsTest) {
            $reflector                  = new ReflectionClass(RequirementsTest::class);
            $this->fileRequirementsTest = realpath($reflector->getFileName());
        }

        return $this->fileRequirementsTest;
    }
}
