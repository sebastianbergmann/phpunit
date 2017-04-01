<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

use PharIo\Version\VersionConstraint;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;

class TestTest extends TestCase
{
    /**
     * @todo Split up in separate tests
     */
    public function testGetExpectedException()
    {
        $this->assertArraySubset(
            ['class' => 'FooBarBaz', 'code' => null, 'message' => ''],
            Test::getExpectedException(\ExceptionTest::class, 'testOne')
        );

        $this->assertArraySubset(
            ['class' => 'Foo_Bar_Baz', 'code' => null, 'message' => ''],
            Test::getExpectedException(\ExceptionTest::class, 'testTwo')
        );

        $this->assertArraySubset(
            ['class' => \Foo\Bar\Baz::class, 'code' => null, 'message' => ''],
            Test::getExpectedException(\ExceptionTest::class, 'testThree')
        );

        $this->assertArraySubset(
            ['class' => 'ほげ', 'code' => null, 'message' => ''],
            Test::getExpectedException(\ExceptionTest::class, 'testFour')
        );

        $this->assertArraySubset(
            ['class' => 'Class', 'code' => 1234, 'message' => 'Message'],
            Test::getExpectedException(\ExceptionTest::class, 'testFive')
        );

        $this->assertArraySubset(
            ['class' => 'Class', 'code' => 1234, 'message' => 'Message'],
            Test::getExpectedException(\ExceptionTest::class, 'testSix')
        );

        $this->assertArraySubset(
            ['class' => 'Class', 'code' => 'ExceptionCode', 'message' => 'Message'],
            Test::getExpectedException(\ExceptionTest::class, 'testSeven')
        );

        $this->assertArraySubset(
            ['class' => 'Class', 'code' => 0, 'message' => 'Message'],
            Test::getExpectedException(\ExceptionTest::class, 'testEight')
        );

        $this->assertArraySubset(
            ['class' => 'Class', 'code' => \ExceptionTest::ERROR_CODE, 'message' => \ExceptionTest::ERROR_MESSAGE],
            Test::getExpectedException(\ExceptionTest::class, 'testNine')
        );

        $this->assertArraySubset(
            ['class' => 'Class', 'code' => null, 'message' => ''],
            Test::getExpectedException(\ExceptionTest::class, 'testSingleLine')
        );

        $this->assertArraySubset(
            ['class' => 'Class', 'code' => \My\Space\ExceptionNamespaceTest::ERROR_CODE, 'message' => \My\Space\ExceptionNamespaceTest::ERROR_MESSAGE],
            Test::getExpectedException(\My\Space\ExceptionNamespaceTest::class, 'testConstants')
        );

        // Ensure the Class::CONST expression is only evaluated when the constant really exists
        $this->assertArraySubset(
            ['class' => 'Class', 'code' => 'ExceptionTest::UNKNOWN_CODE_CONSTANT', 'message' => 'ExceptionTest::UNKNOWN_MESSAGE_CONSTANT'],
            Test::getExpectedException(\ExceptionTest::class, 'testUnknownConstants')
        );

        $this->assertArraySubset(
            ['class' => 'Class', 'code' => 'My\Space\ExceptionNamespaceTest::UNKNOWN_CODE_CONSTANT', 'message' => 'My\Space\ExceptionNamespaceTest::UNKNOWN_MESSAGE_CONSTANT'],
            Test::getExpectedException(\My\Space\ExceptionNamespaceTest::class, 'testUnknownConstants')
        );
    }

    public function testGetExpectedRegExp()
    {
        $this->assertArraySubset(
            ['message_regex' => '#regex#'],
            Test::getExpectedException(\ExceptionTest::class, 'testWithRegexMessage')
        );

        $this->assertArraySubset(
            ['message_regex' => '#regex#'],
            Test::getExpectedException(\ExceptionTest::class, 'testWithRegexMessageFromClassConstant')
        );

        $this->assertArraySubset(
            ['message_regex' => 'ExceptionTest::UNKNOWN_MESSAGE_REGEX_CONSTANT'],
            Test::getExpectedException(\ExceptionTest::class, 'testWithUnknowRegexMessageFromClassConstant')
        );
    }

    /**
     * @dataProvider requirementsProvider
     */
    public function testGetRequirements($test, $result)
    {
        $this->assertEquals(
            $result,
            Test::getRequirements(\RequirementsTest::class, $test)
        );
    }

    public function requirementsProvider()
    {
        return [
            ['testOne',    []],
            ['testTwo',    ['PHPUnit'    => ['version' => '1.0', 'operator' => '']]],
            ['testThree',  ['PHP'        => ['version' => '2.0', 'operator' => '']]],
            ['testFour',   [
                'PHPUnit'    => ['version' => '2.0', 'operator' => ''],
                'PHP'        => ['version' => '1.0', 'operator' => '']]
            ],
            ['testFive',   ['PHP'        => ['version' => '5.4.0RC6', 'operator' => '']]],
            ['testSix',    ['PHP'        => ['version' => '5.4.0-alpha1', 'operator' => '']]],
            ['testSeven',  ['PHP'        => ['version' => '5.4.0beta2', 'operator' => '']]],
            ['testEight',  ['PHP'        => ['version' => '5.4-dev', 'operator' => '']]],
            ['testNine',   ['functions'  => ['testFunc']]],
            ['testTen',    ['extensions' => ['testExt']]],
            ['testEleven', ['OS'         => '/Linux/i']],
            [
              'testSpace',
              [
                'extensions' => ['spl'],
                'OS'         => '/.*/i'
              ]
            ],
            [
              'testAllPossibleRequirements',
              [
                'PHP'       => ['version' => '99-dev', 'operator' => ''],
                'PHPUnit'   => ['version' => '9-dev', 'operator' => ''],
                'OS'        => '/DOESNOTEXIST/i',
                'functions' => [
                  'testFuncOne',
                  'testFuncTwo',
                ],
                'extensions' => [
                  'testExtOne',
                  'testExtTwo',
                  'testExtThree',
                ],
                'extension_versions' => [
                    'testExtThree' => ['version' => '2.0', 'operator' => '']
                ]
              ]
            ],
            ['testSpecificExtensionVersion',
                [
                    'extension_versions' => ['testExt' => ['version' => '1.8.0', 'operator' => '']],
                    'extensions'         => ['testExt']
                ]
            ],
            ['testPHPVersionOperatorLessThan',
                [
                    'PHP' => ['version' => '5.4', 'operator' => '<']
                ]
            ],
            ['testPHPVersionOperatorLessThanEquals',
                [
                    'PHP' => ['version' => '5.4', 'operator' => '<=']
                ]
            ],
            ['testPHPVersionOperatorGreaterThan',
                [
                    'PHP' => ['version' => '99', 'operator' => '>']
                ]
            ],
            ['testPHPVersionOperatorGreaterThanEquals',
                [
                    'PHP' => ['version' => '99', 'operator' => '>=']
                ]
            ],
            ['testPHPVersionOperatorEquals',
                [
                    'PHP' => ['version' => '5.4', 'operator' => '=']
                ]
            ],
            ['testPHPVersionOperatorDoubleEquals',
                [
                    'PHP' => ['version' => '5.4', 'operator' => '==']
                ]
            ],
            ['testPHPVersionOperatorBangEquals',
                [
                    'PHP' => ['version' => '99', 'operator' => '!=']
                ]
            ],
            ['testPHPVersionOperatorNotEquals',
                [
                    'PHP' => ['version' => '99', 'operator' => '<>']
                ]
            ],
            ['testPHPVersionOperatorNoSpace',
                [
                    'PHP' => ['version' => '99', 'operator' => '>=']
                ]
            ],
            ['testPHPUnitVersionOperatorLessThan',
                [
                    'PHPUnit' => ['version' => '1.0', 'operator' => '<']
                ]
            ],
            ['testPHPUnitVersionOperatorLessThanEquals',
                [
                    'PHPUnit' => ['version' => '1.0', 'operator' => '<=']
                ]
            ],
            ['testPHPUnitVersionOperatorGreaterThan',
                [
                    'PHPUnit' => ['version' => '99', 'operator' => '>']
                ]
            ],
            ['testPHPUnitVersionOperatorGreaterThanEquals',
                [
                    'PHPUnit' => ['version' => '99', 'operator' => '>=']
                ]
            ],
            ['testPHPUnitVersionOperatorEquals',
                [
                    'PHPUnit' => ['version' => '1.0', 'operator' => '=']
                ]
            ],
            ['testPHPUnitVersionOperatorDoubleEquals',
                [
                    'PHPUnit' => ['version' => '1.0', 'operator' => '==']
                ]
            ],
            ['testPHPUnitVersionOperatorBangEquals',
                [
                    'PHPUnit' => ['version' => '99', 'operator' => '!=']
                ]
            ],
            ['testPHPUnitVersionOperatorNotEquals',
                [
                    'PHPUnit' => ['version' => '99', 'operator' => '<>']
                ]
            ],
            ['testPHPUnitVersionOperatorNoSpace',
                [
                    'PHPUnit' => ['version' => '99', 'operator' => '>=']
                ]
            ],
            ['testExtensionVersionOperatorLessThanEquals',
                [
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '1.0', 'operator' => '<=']]
                ]
            ],
            ['testExtensionVersionOperatorGreaterThan',
                [
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '>']]
                ]
            ],
            ['testExtensionVersionOperatorGreaterThanEquals',
                [
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '>=']]
                ]
            ],
            ['testExtensionVersionOperatorEquals',
                [
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '1.0', 'operator' => '=']]
                ]
            ],
            ['testExtensionVersionOperatorDoubleEquals',
                [
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '1.0', 'operator' => '==']]
                ]
            ],
            ['testExtensionVersionOperatorBangEquals',
                [
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '!=']]
                ]
            ],
            ['testExtensionVersionOperatorNotEquals',
                [
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '<>']]
                ]
            ],
            ['testExtensionVersionOperatorNoSpace',
                [
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '>=']]
                ]
            ]
        ];
    }

    /**
     * @dataProvider requirementsWithVersionConstraintsProvider
     */
    public function testGetRequirementsWithVersionConstraints($test, $result)
    {
        $requirements = Test::getRequirements(\RequirementsTest::class, $test);
        foreach ($result as $type => $expected_requirement) {
            $this->assertArrayHasKey(
                "{$type}_constraint",
                $requirements
            );
            $this->assertArrayHasKey(
                'constraint',
                $requirements["{$type}_constraint"]
            );
            $this->assertInstanceOf(
                VersionConstraint::class,
                $requirements["{$type}_constraint"]['constraint']
            );
            $this->assertSame(
                $expected_requirement['constraint'],
                $requirements["{$type}_constraint"]['constraint']->asString()
            );
        }
    }

    public function requirementsWithVersionConstraintsProvider()
    {
        return [
            [
                'testVersionConstraintTildeMajor',
                [
                    'PHP' => [
                        'constraint' => '~1.0'
                    ],
                    'PHPUnit' => [
                        'constraint' => '~2.0'
                    ]
                ]
            ],
            [
                'testVersionConstraintCaretMajor',
                [
                    'PHP' => [
                        'constraint' => '^1.0'
                    ],
                    'PHPUnit' => [
                        'constraint' => '^2.0'
                    ]
                ]
            ],
            [
                'testVersionConstraintTildeMinor',
                [
                    'PHP' => [
                        'constraint' => '~3.4.7'
                    ],
                    'PHPUnit' => [
                        'constraint' => '~4.7.1'
                    ]
                ]
            ],
            [
                'testVersionConstraintCaretMinor',
                [
                    'PHP' => [
                        'constraint' => '^7.0.17'
                    ],
                    'PHPUnit' => [
                        'constraint' => '^4.7.1'
                    ]
                ]
            ],
            [
                'testVersionConstraintCaretOr',
                [
                    'PHP' => [
                        'constraint' => '^5.6 || ^7.0'
                    ],
                    'PHPUnit' => [
                        'constraint' => '^5.0 || ^6.0'
                    ]
                ]
            ],
            [
                'testVersionConstraintTildeOr',
                [
                    'PHP' => [
                        'constraint' => '~5.6.22 || ~7.0.17'
                    ],
                    'PHPUnit' => [
                        'constraint' => '^5.0.5 || ^6.0.6'
                    ]
                ]
            ],
            [
                'testVersionConstraintTildeOrCaret',
                [
                    'PHP' => [
                        'constraint' => '~5.6.22 || ^7.0'
                    ],
                    'PHPUnit' => [
                        'constraint' => '~5.6.22 || ^7.0'
                    ]
                ]
            ],
            [
                'testVersionConstraintCaretOrTilde',
                [
                    'PHP' => [
                        'constraint' => '^5.6 || ~7.0.17'
                    ],
                    'PHPUnit' => [
                        'constraint' => '^5.6 || ~7.0.17'
                    ]
                ]
            ],
            [
                'testVersionConstraintRegexpIgnoresWhitespace',
                [
                    'PHP' => [
                        'constraint' => '~5.6.22 || ~7.0.17'
                    ],
                    'PHPUnit' => [
                        'constraint' => '~5.6.22 || ~7.0.17'
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider requirementsWithInvalidVersionConstraintsThrowsExceptionProvider
     */
    public function testGetRequirementsWithInvalidVersionConstraintsThrowsException($test)
    {
        $this->expectException(Warning::class);
        Test::getRequirements(\RequirementsTest::class, $test);
    }

    public function requirementsWithInvalidVersionConstraintsThrowsExceptionProvider()
    {
        return [
            ['testVersionConstraintInvalidPhpConstraint'],
            ['testVersionConstraintInvalidPhpUnitConstraint']
        ];
    }

    public function testGetRequirementsMergesClassAndMethodDocBlocks()
    {
        $expectedAnnotations = [
            'PHP'       => ['version' => '5.4', 'operator' => ''],
            'PHPUnit'   => ['version' => '3.7', 'operator' => ''],
            'OS'        => '/WINNT/i',
            'functions' => [
              'testFuncClass',
              'testFuncMethod',
            ],
            'extensions' => [
              'testExtClass',
              'testExtMethod',
            ]
        ];

        $this->assertEquals(
            $expectedAnnotations,
            Test::getRequirements(\RequirementsClassDocBlockTest::class, 'testMethod')
        );
    }

    /**
     * @dataProvider missingRequirementsProvider
     */
    public function testGetMissingRequirements($test, $result)
    {
        $this->assertEquals(
            $result,
            Test::getMissingRequirements(\RequirementsTest::class, $test)
        );
    }

    public function missingRequirementsProvider()
    {
        return [
            ['testOne',            []],
            ['testNine',           ['Function testFunc is required.']],
            ['testTen',            ['Extension testExt is required.']],
            ['testAlwaysSkip',     ['PHPUnit >= 1111111 is required.']],
            ['testAlwaysSkip2',    ['PHP >= 9999999 is required.']],
            ['testAlwaysSkip3',    ['Operating system matching /DOESNOTEXIST/i is required.']],
            ['testAllPossibleRequirements', [
              'PHP >= 99-dev is required.',
              'PHPUnit >= 9-dev is required.',
              'Operating system matching /DOESNOTEXIST/i is required.',
              'Function testFuncOne is required.',
              'Function testFuncTwo is required.',
              'Extension testExtOne is required.',
              'Extension testExtTwo is required.',
              'Extension testExtThree >= 2.0 is required.',
            ]],
            ['testPHPVersionOperatorLessThan', ['PHP < 5.4 is required.']],
            ['testPHPVersionOperatorLessThanEquals', ['PHP <= 5.4 is required.']],
            ['testPHPVersionOperatorGreaterThan', ['PHP > 99 is required.']],
            ['testPHPVersionOperatorGreaterThanEquals', ['PHP >= 99 is required.']],
            ['testPHPVersionOperatorNoSpace', ['PHP >= 99 is required.']],
            ['testPHPVersionOperatorEquals', ['PHP = 5.4 is required.']],
            ['testPHPVersionOperatorDoubleEquals', ['PHP == 5.4 is required.']],
            ['testPHPUnitVersionOperatorLessThan', ['PHPUnit < 1.0 is required.']],
            ['testPHPUnitVersionOperatorLessThanEquals', ['PHPUnit <= 1.0 is required.']],
            ['testPHPUnitVersionOperatorGreaterThan', ['PHPUnit > 99 is required.']],
            ['testPHPUnitVersionOperatorGreaterThanEquals', ['PHPUnit >= 99 is required.']],
            ['testPHPUnitVersionOperatorEquals', ['PHPUnit = 1.0 is required.']],
            ['testPHPUnitVersionOperatorDoubleEquals', ['PHPUnit == 1.0 is required.']],
            ['testPHPUnitVersionOperatorNoSpace', ['PHPUnit >= 99 is required.']],
            ['testExtensionVersionOperatorLessThan', ['Extension testExtOne < 1.0 is required.']],
            ['testExtensionVersionOperatorLessThanEquals', ['Extension testExtOne <= 1.0 is required.']],
            ['testExtensionVersionOperatorGreaterThan', ['Extension testExtOne > 99 is required.']],
            ['testExtensionVersionOperatorGreaterThanEquals', ['Extension testExtOne >= 99 is required.']],
            ['testExtensionVersionOperatorEquals', ['Extension testExtOne = 1.0 is required.']],
            ['testExtensionVersionOperatorDoubleEquals', ['Extension testExtOne == 1.0 is required.']],
            ['testExtensionVersionOperatorNoSpace', ['Extension testExtOne >= 99 is required.']],
            ['testVersionConstraintTildeMajor', [
                'PHP version does not match the required constraint ~1.0.',
                'PHPUnit version does not match the required constraint ~2.0.'
            ]],
            ['testVersionConstraintCaretMajor', [
                'PHP version does not match the required constraint ^1.0.',
                'PHPUnit version does not match the required constraint ^2.0.'
            ]]
        ];
    }

    /**
     * @todo   This test does not really test functionality of \PHPUnit\Util\Test
     */
    public function testGetProvidedDataRegEx()
    {
        $result = \preg_match(Test::REGEX_DATA_PROVIDER, '@dataProvider method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('method', $matches[1]);

        $result = \preg_match(Test::REGEX_DATA_PROVIDER, '@dataProvider class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('class::method', $matches[1]);

        $result = \preg_match(Test::REGEX_DATA_PROVIDER, '@dataProvider namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\class::method', $matches[1]);

        $result = \preg_match(Test::REGEX_DATA_PROVIDER, '@dataProvider namespace\namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\namespace\class::method', $matches[1]);

        $result = \preg_match(Test::REGEX_DATA_PROVIDER, '@dataProvider メソッド', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('メソッド', $matches[1]);
    }

    /**
     * Check if all data providers are being merged.
     */
    public function testMultipleDataProviders()
    {
        $dataSets = Test::getProvidedData(\MultipleDataProviderTest::class, 'testOne');

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

    public function testMultipleYieldIteratorDataProviders()
    {
        $dataSets = Test::getProvidedData(\MultipleDataProviderTest::class, 'testTwo');

        $this->assertEquals(9, \count($dataSets));

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

    public function testTestWithEmptyAnnotation()
    {
        $result = Test::getDataFromTestWithAnnotation("/**\n * @anotherAnnotation\n */");
        $this->assertNull($result);
    }

    public function testTestWithSimpleCase()
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith [1]
                                                                     */');
        $this->assertEquals([[1]], $result);
    }

    public function testTestWithMultiLineMultiParameterCase()
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith [1, 2]
                                                                     * [3, 4]
                                                                     */');
        $this->assertEquals([[1, 2], [3, 4]], $result);
    }

    public function testTestWithVariousTypes()
    {
        $result = Test::getDataFromTestWithAnnotation('/**
            * @testWith ["ab"]
            *           [true]
            *           [null]
         */');
        $this->assertEquals([['ab'], [true], [null]], $result);
    }

    public function testTestWithAnnotationAfter()
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith [1]
                                                                     *           [2]
                                                                     * @annotation
                                                                     */');
        $this->assertEquals([[1], [2]], $result);
    }

    public function testTestWithSimpleTextAfter()
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith [1]
                                                                     *           [2]
                                                                     * blah blah
                                                                     */');
        $this->assertEquals([[1], [2]], $result);
    }

    public function testTestWithCharacterEscape()
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith ["\"", "\""]
                                                                     */');
        $this->assertEquals([['"', '"']], $result);
    }

    public function testTestWithThrowsProperExceptionIfDatasetCannotBeParsed()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageRegExp('/^The dataset for the @testWith annotation cannot be parsed:/');

        Test::getDataFromTestWithAnnotation('/**
                                                           * @testWith [s]
                                                           */');
    }

    public function testTestWithThrowsProperExceptionIfMultiLineDatasetCannotBeParsed()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageRegExp('/^The dataset for the @testWith annotation cannot be parsed:/');

        Test::getDataFromTestWithAnnotation('/**
                                                           * @testWith ["valid"]
                                                           *           [invalid]
                                                           */');
    }

    /**
     * @todo Not sure what this test tests (name is misleading at least)
     */
    public function testParseAnnotation()
    {
        $this->assertEquals(
            ['Foo', 'ほげ'],
            Test::getDependencies(\get_class($this), 'methodForTestParseAnnotation')
        );
    }

    /**
     * @depends Foo
     * @depends ほげ
     *
     * @todo Remove fixture from test class
     */
    public function methodForTestParseAnnotation()
    {
    }

    public function testParseAnnotationThatIsOnlyOneLine()
    {
        $this->assertEquals(
            ['Bar'],
            Test::getDependencies(\get_class($this), 'methodForTestParseAnnotationThatIsOnlyOneLine')
        );
    }

    /** @depends Bar */
    public function methodForTestParseAnnotationThatIsOnlyOneLine()
    {
        // TODO Remove fixture from test class
    }

    /**
     * @dataProvider getLinesToBeCoveredProvider
     */
    public function testGetLinesToBeCovered($test, $lines)
    {
        if (\strpos($test, 'Namespace') === 0) {
            $expected = [
              TEST_FILES_PATH . 'NamespaceCoveredClass.php' => $lines
            ];
        } elseif ($test === 'CoverageNoneTest') {
            $expected = [];
        } elseif ($test === 'CoverageNothingTest') {
            $expected = false;
        } elseif ($test === 'CoverageFunctionTest') {
            $expected = [
              TEST_FILES_PATH . 'CoveredFunction.php' => $lines
            ];
        } else {
            $expected = [TEST_FILES_PATH . 'CoveredClass.php' => $lines];
        }

        $this->assertEquals(
            $expected,
            Test::getLinesToBeCovered(
                $test,
                'testSomething'
            )
        );
    }

    public function testGetLinesToBeCovered2()
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            'NotExistingCoveredElementTest',
            'testOne'
        );
    }

    public function testGetLinesToBeCovered3()
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            'NotExistingCoveredElementTest',
            'testTwo'
        );
    }

    public function testGetLinesToBeCovered4()
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            'NotExistingCoveredElementTest',
            'testThree'
        );
    }

    public function testGetLinesToBeCoveredSkipsNonExistentMethods()
    {
        $this->assertSame(
            [],
            Test::getLinesToBeCovered(
                'NotExistingCoveredElementTest',
                'methodDoesNotExist'
            )
        );
    }

    public function testTwoCoversDefaultClassAnnoationsAreNotAllowed()
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            'CoverageTwoDefaultClassAnnotations',
            'testSomething'
        );
    }

    public function testFunctionParenthesesAreAllowed()
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => \range(2, 4)],
            Test::getLinesToBeCovered(
                'CoverageFunctionParenthesesTest',
                'testSomething'
            )
        );
    }

    public function testFunctionParenthesesAreAllowedWithWhitespace()
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => \range(2, 4)],
            Test::getLinesToBeCovered(
                'CoverageFunctionParenthesesWhitespaceTest',
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowed()
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => \range(31, 35)],
            Test::getLinesToBeCovered(
                'CoverageMethodParenthesesTest',
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowedWithWhitespace()
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => \range(31, 35)],
            Test::getLinesToBeCovered(
                'CoverageMethodParenthesesWhitespaceTest',
                'testSomething'
            )
        );
    }

    public function testNamespacedFunctionCanBeCoveredOrUsed()
    {
        $this->assertEquals(
            [
                TEST_FILES_PATH . 'NamespaceCoveredFunction.php' => \range(4, 7)
            ],
            Test::getLinesToBeCovered(
                \CoverageNamespacedFunctionTest::class,
                'testFunc'
            )
        );
    }

    public function getLinesToBeCoveredProvider()
    {
        return [
          [
            'CoverageNoneTest',
            []
          ],
          [
            'CoverageClassExtendedTest',
            \array_merge(\range(19, 36), \range(2, 17))
          ],
          [
            'CoverageClassTest',
            \range(19, 36)
          ],
          [
            'CoverageMethodTest',
            \range(31, 35)
          ],
          [
            'CoverageMethodOneLineAnnotationTest',
            \range(31, 35)
          ],
          [
            'CoverageNotPrivateTest',
            \array_merge(\range(25, 29), \range(31, 35))
          ],
          [
            'CoverageNotProtectedTest',
            \array_merge(\range(21, 23), \range(31, 35))
          ],
          [
            'CoverageNotPublicTest',
            \array_merge(\range(21, 23), \range(25, 29))
          ],
          [
            'CoveragePrivateTest',
            \range(21, 23)
          ],
          [
            'CoverageProtectedTest',
            \range(25, 29)
          ],
          [
            'CoveragePublicTest',
            \range(31, 35)
          ],
          [
            'CoverageFunctionTest',
            \range(2, 4)
          ],
          [
            'NamespaceCoverageClassExtendedTest',
            \array_merge(\range(21, 38), \range(4, 19))
          ],
          [
            'NamespaceCoverageClassTest',
            \range(21, 38)
          ],
          [
            'NamespaceCoverageMethodTest',
            \range(33, 37)
          ],
          [
            'NamespaceCoverageNotPrivateTest',
            \array_merge(\range(27, 31), \range(33, 37))
          ],
          [
            'NamespaceCoverageNotProtectedTest',
            \array_merge(\range(23, 25), \range(33, 37))
          ],
          [
            'NamespaceCoverageNotPublicTest',
            \array_merge(\range(23, 25), \range(27, 31))
          ],
          [
            'NamespaceCoveragePrivateTest',
            \range(23, 25)
          ],
          [
            'NamespaceCoverageProtectedTest',
            \range(27, 31)
          ],
          [
            'NamespaceCoveragePublicTest',
            \range(33, 37)
          ],
          [
            'NamespaceCoverageCoversClassTest',
            \array_merge(\range(23, 25), \range(27, 31), \range(33, 37), \range(6, 8), \range(10, 13), \range(15, 18))
          ],
          [
            'NamespaceCoverageCoversClassPublicTest',
            \range(33, 37)
          ],
          [
            'CoverageNothingTest',
            false
          ]
        ];
    }

    public function testParseTestMethodAnnotationsIncorporatesTraits()
    {
        $result = Test::parseTestMethodAnnotations(\ParseTestMethodAnnotationsMock::class);

        $this->assertArrayHasKey('class', $result);
        $this->assertArrayHasKey('method', $result);
        $this->assertArrayHasKey('theClassAnnotation', $result['class']);
        $this->assertArrayHasKey('theTraitAnnotation', $result['class']);
    }
}
