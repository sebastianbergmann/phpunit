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

use PharIo\Version\VersionConstraint;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;

class TestTest extends TestCase
{
    /**
     * @var string
     */
    private $fileRequirementsTest;

    /**
     * @testdox Test::getRequirements() for $test
     * @dataProvider requirementsProvider
     *
     * @throws Warning
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetRequirements($test, $result): void
    {
        $this->assertEquals(
            $result,
            Test::getRequirements(\RequirementsTest::class, $test)
        );
    }

    public function requirementsProvider(): array
    {
        return [
            [
                'testOne',
                ['__OFFSET' => [
                    '__FILE' => $this->getRequirementsTestClassFile(),
                ]],
            ],

            [
                'testTwo',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 19,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => ''],
                ],
            ],

            [
                'testThree',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 26,
                    ],
                    'PHP'        => ['version' => '2.0', 'operator' => ''],
                ],
            ],

            [
                'testFour',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 33,
                        'PHP'     => 34,
                    ],
                    'PHPUnit'    => ['version' => '2.0', 'operator' => ''],
                    'PHP'        => ['version' => '1.0', 'operator' => ''],
                ],
            ],

            [
                'testFive',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 41,
                    ],
                    'PHP'        => ['version' => '5.4.0RC6', 'operator' => ''],
                ],
            ],

            [
                'testSix',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 48,
                    ],
                    'PHP'        => ['version' => '5.4.0-alpha1', 'operator' => ''],
                ],
            ],

            [
                'testSeven',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 55,
                    ],
                    'PHP'        => ['version' => '5.4.0beta2', 'operator' => ''],
                ],
            ],

            [
                'testEight',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 62,
                    ],
                    'PHP'        => ['version' => '5.4-dev', 'operator' => ''],
                ],
            ],

            [
                'testNine',
                [
                    '__OFFSET' => [
                        '__FILE'            => $this->getRequirementsTestClassFile(),
                        'function_testFunc' => 69,
                    ],
                    'functions'  => ['testFunc'],
                ],
            ],

            [
                'testTen',
                [
                    '__OFFSET' => [
                        '__FILE'            => $this->getRequirementsTestClassFile(),
                        'extension_testExt' => 85,
                    ],
                    'extensions' => ['testExt'],
                ],
            ],

            [
                'testEleven',
                [
                    '__OFFSET' => [
                        '__FILE'   => $this->getRequirementsTestClassFile(),
                        'OS'       => 92,
                        'OSFAMILY' => 93,
                    ],
                    'OS'         => 'SunOS',
                    'OSFAMILY'   => 'Solaris',
                ],
            ],

            [
                'testSpace',
                [
                    '__OFFSET' => [
                        '__FILE'        => $this->getRequirementsTestClassFile(),
                        'extension_spl' => 171,
                        'OS'            => 172,
                    ],
                    'extensions' => ['spl'],
                    'OS'         => '.*',
                ],
            ],

            [
                'testAllPossibleRequirements',
                [
                    '__OFFSET' => [
                        '__FILE'                  => $this->getRequirementsTestClassFile(),
                        'PHP'                     => 100,
                        'PHPUnit'                 => 101,
                        'OS'                      => 102,
                        'function_testFuncOne'    => 103,
                        'function_testFunc2'      => 104,
                        'extension_testExtOne'    => 105,
                        'extension_testExt2'      => 106,
                        'extension_testExtThree'  => 107,
                        '__SETTING_not_a_setting' => 108,
                    ],
                    'PHP'       => ['version' => '99-dev', 'operator' => ''],
                    'PHPUnit'   => ['version' => '9-dev', 'operator' => ''],
                    'OS'        => 'DOESNOTEXIST',
                    'functions' => [
                        'testFuncOne',
                        'testFunc2',
                    ],
                    'setting'   => [
                        'not_a_setting' => 'Off',
                    ],
                    'extensions' => [
                        'testExtOne',
                        'testExt2',
                        'testExtThree',
                    ],
                    'extension_versions' => [
                        'testExtThree' => ['version' => '2.0', 'operator' => ''],
                    ],
                ],
            ],

            ['testSpecificExtensionVersion',
                [
                    '__OFFSET' => [
                        '__FILE'            => $this->getRequirementsTestClassFile(),
                        'extension_testExt' => 179,
                    ],
                    'extension_versions' => ['testExt' => ['version' => '1.8.0', 'operator' => '']],
                    'extensions'         => ['testExt'],
                ],
            ],
            ['testPHPVersionOperatorLessThan',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 187,
                    ],
                    'PHP' => ['version' => '5.4', 'operator' => '<'],
                ],
            ],
            ['testPHPVersionOperatorLessThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 195,
                    ],
                    'PHP' => ['version' => '5.4', 'operator' => '<='],
                ],
            ],
            ['testPHPVersionOperatorGreaterThan',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 203,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '>'],
                ],
            ],
            ['testPHPVersionOperatorGreaterThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 211,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '>='],
                ],
            ],
            ['testPHPVersionOperatorEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 219,
                    ],
                    'PHP' => ['version' => '5.4', 'operator' => '='],
                ],
            ],
            ['testPHPVersionOperatorDoubleEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 227,
                    ],
                    'PHP' => ['version' => '5.4', 'operator' => '=='],
                ],
            ],
            ['testPHPVersionOperatorBangEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 235,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '!='],
                ],
            ],
            ['testPHPVersionOperatorNotEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 243,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '<>'],
                ],
            ],
            ['testPHPVersionOperatorNoSpace',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 251,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '>='],
                ],
            ],
            ['testPHPUnitVersionOperatorLessThan',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 259,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => '<'],
                ],
            ],
            ['testPHPUnitVersionOperatorLessThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 267,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => '<='],
                ],
            ],
            ['testPHPUnitVersionOperatorGreaterThan',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 275,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '>'],
                ],
            ],
            ['testPHPUnitVersionOperatorGreaterThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 283,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '>='],
                ],
            ],
            ['testPHPUnitVersionOperatorEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 291,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => '='],
                ],
            ],
            ['testPHPUnitVersionOperatorDoubleEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 299,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => '=='],
                ],
            ],
            ['testPHPUnitVersionOperatorBangEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 307,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '!='],
                ],
            ],
            ['testPHPUnitVersionOperatorNotEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 315,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '<>'],
                ],
            ],
            ['testPHPUnitVersionOperatorNoSpace',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 323,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '>='],
                ],
            ],
            ['testExtensionVersionOperatorLessThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 337,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '1.0', 'operator' => '<=']],
                ],
            ],
            ['testExtensionVersionOperatorGreaterThan',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 344,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '>']],
                ],
            ],
            ['testExtensionVersionOperatorGreaterThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 351,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '>=']],
                ],
            ],
            ['testExtensionVersionOperatorEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 358,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '1.0', 'operator' => '=']],
                ],
            ],
            ['testExtensionVersionOperatorDoubleEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 365,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '1.0', 'operator' => '==']],
                ],
            ],
            ['testExtensionVersionOperatorBangEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 372,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '!=']],
                ],
            ],
            ['testExtensionVersionOperatorNotEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 379,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '<>']],
                ],
            ],
            ['testExtensionVersionOperatorNoSpace',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->fileRequirementsTest,
                        'extension_testExtOne' => 386,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '>=']],
                ],
            ],
        ];
    }

    /**
     * @testdox Test::getRequirements() with constraints for $test
     * @dataProvider requirementsWithVersionConstraintsProvider
     *
     * @throws Exception
     * @throws Warning
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetRequirementsWithVersionConstraints($test, array $result): void
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

    public function requirementsWithVersionConstraintsProvider(): array
    {
        return [
            [
                'testVersionConstraintTildeMajor',
                [
                    'PHP' => [
                        'constraint' => '~1.0',
                    ],
                    'PHPUnit' => [
                        'constraint' => '~2.0',
                    ],
                ],
            ],
            [
                'testVersionConstraintCaretMajor',
                [
                    'PHP' => [
                        'constraint' => '^1.0',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^2.0',
                    ],
                ],
            ],
            [
                'testVersionConstraintTildeMinor',
                [
                    'PHP' => [
                        'constraint' => '~3.4.7',
                    ],
                    'PHPUnit' => [
                        'constraint' => '~4.7.1',
                    ],
                ],
            ],
            [
                'testVersionConstraintCaretMinor',
                [
                    'PHP' => [
                        'constraint' => '^7.0.17',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^4.7.1',
                    ],
                ],
            ],
            [
                'testVersionConstraintCaretOr',
                [
                    'PHP' => [
                        'constraint' => '^5.6 || ^7.0',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^5.0 || ^6.0',
                    ],
                ],
            ],
            [
                'testVersionConstraintTildeOr',
                [
                    'PHP' => [
                        'constraint' => '~5.6.22 || ~7.0.17',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^5.0.5 || ^6.0.6',
                    ],
                ],
            ],
            [
                'testVersionConstraintTildeOrCaret',
                [
                    'PHP' => [
                        'constraint' => '~5.6.22 || ^7.0',
                    ],
                    'PHPUnit' => [
                        'constraint' => '~5.6.22 || ^7.0',
                    ],
                ],
            ],
            [
                'testVersionConstraintCaretOrTilde',
                [
                    'PHP' => [
                        'constraint' => '^5.6 || ~7.0.17',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^5.6 || ~7.0.17',
                    ],
                ],
            ],
            [
                'testVersionConstraintRegexpIgnoresWhitespace',
                [
                    'PHP' => [
                        'constraint' => '~5.6.22 || ~7.0.17',
                    ],
                    'PHPUnit' => [
                        'constraint' => '~5.6.22 || ~7.0.17',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider requirementsWithInvalidVersionConstraintsThrowsExceptionProvider
     *
     * @throws Warning
     */
    public function testGetRequirementsWithInvalidVersionConstraintsThrowsException($test): void
    {
        $this->expectException(Warning::class);
        Test::getRequirements(\RequirementsTest::class, $test);
    }

    public function requirementsWithInvalidVersionConstraintsThrowsExceptionProvider(): array
    {
        return [
            ['testVersionConstraintInvalidPhpConstraint'],
            ['testVersionConstraintInvalidPhpUnitConstraint'],
        ];
    }

    public function testGetRequirementsMergesClassAndMethodDocBlocks(): void
    {
        $reflector = new \ReflectionClass(\RequirementsClassDocBlockTest::class);
        $file      = $reflector->getFileName();

        $expectedAnnotations = [
            '__OFFSET' => [
                '__FILE'                  => $file,
                'PHP'                     => 21,
                'PHPUnit'                 => 22,
                'OS'                      => 23,
                'function_testFuncClass'  => 15,
                'extension_testExtClass'  => 16,
                'function_testFuncMethod' => 24,
                'extension_testExtMethod' => 25,
            ],
            'PHP'       => ['version' => '5.4', 'operator' => ''],
            'PHPUnit'   => ['version' => '3.7', 'operator' => ''],
            'OS'        => 'WINNT',
            'functions' => [
                'testFuncClass',
                'testFuncMethod',
            ],
            'extensions' => [
                'testExtClass',
                'testExtMethod',
            ],
        ];

        $this->assertEquals(
            $expectedAnnotations,
            Test::getRequirements(\RequirementsClassDocBlockTest::class, 'testMethod')
        );
    }

    /**
     * @testdox Test::getMissingRequirements() for $test
     * @dataProvider missingRequirementsProvider
     *
     * @throws Warning
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetMissingRequirements($test, $result): void
    {
        $this->assertEquals(
            $result,
            Test::getMissingRequirements(\RequirementsTest::class, $test)
        );
    }

    public function missingRequirementsProvider(): array
    {
        return [
            ['testOne',            []],
            ['testNine',           [
                '__OFFSET_LINE=69',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Function testFunc is required.',
            ]],
            ['testTen',            [
                '__OFFSET_LINE=85',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExt is required.',
            ]],
            ['testAlwaysSkip',     [
                '__OFFSET_LINE=143',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit >= 1111111 is required.',
            ]],
            ['testAlwaysSkip2',    [
                '__OFFSET_LINE=150',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 9999999 is required.',
            ]],
            ['testAlwaysSkip3',    [
                '__OFFSET_LINE=157',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Operating system matching /DOESNOTEXIST/i is required.',
            ]],
            ['testAllPossibleRequirements', [
                '__OFFSET_LINE=100',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 99-dev is required.',
                'PHPUnit >= 9-dev is required.',
                'Operating system matching /DOESNOTEXIST/i is required.',
                'Function testFuncOne is required.',
                'Function testFunc2 is required.',
                'Setting "not_a_setting" must be "Off".',
                'Extension testExtOne is required.',
                'Extension testExt2 is required.',
                'Extension testExtThree >= 2.0 is required.',
            ]],
            ['testPHPVersionOperatorLessThan', [
                '__OFFSET_LINE=187',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP < 5.4 is required.',
            ]],
            ['testPHPVersionOperatorLessThanEquals', [
                '__OFFSET_LINE=195',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP <= 5.4 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThan', [
                '__OFFSET_LINE=203',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP > 99 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThanEquals', [
                '__OFFSET_LINE=211',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorNoSpace', [
                '__OFFSET_LINE=251',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorEquals', [
                '__OFFSET_LINE=219',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP = 5.4 is required.',
            ]],
            ['testPHPVersionOperatorDoubleEquals', [
                '__OFFSET_LINE=227',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP == 5.4 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThan', [
                '__OFFSET_LINE=259',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit < 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThanEquals', [
                '__OFFSET_LINE=267',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit <= 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThan', [
                '__OFFSET_LINE=275',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit > 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThanEquals', [
                '__OFFSET_LINE=283',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit >= 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorEquals', [
                '__OFFSET_LINE=291',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit = 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorDoubleEquals', [
                '__OFFSET_LINE=299',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit == 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorNoSpace', [
                '__OFFSET_LINE=323',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorLessThan', [
                '__OFFSET_LINE=330',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne < 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorLessThanEquals', [
                '__OFFSET_LINE=337',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne <= 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThan', [
                '__OFFSET_LINE=344',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne > 99 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThanEquals', [
                '__OFFSET_LINE=351',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorEquals', [
                '__OFFSET_LINE=358',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne = 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorDoubleEquals', [
                '__OFFSET_LINE=365',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne == 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorNoSpace', [
                '__OFFSET_LINE=386',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne >= 99 is required.',
            ]],
            ['testVersionConstraintTildeMajor', [
                '__OFFSET_LINE=393',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP version does not match the required constraint ~1.0.',
                'PHPUnit version does not match the required constraint ~2.0.',
            ]],
            ['testVersionConstraintCaretMajor', [
                '__OFFSET_LINE=401',
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
    public function testMultipleDataProviders(): void
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

    public function testMultipleYieldIteratorDataProviders(): void
    {
        $dataSets = Test::getProvidedData(\MultipleDataProviderTest::class, 'testTwo');

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

    public function testWithVariousIterableDataProviders(): void
    {
        $dataSets = Test::getProvidedData(\VariousIterableDataProviderTest::class, 'test');

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

    public function testWithDuplicateKeyDataProviders(): void
    {
        $this->expectException(InvalidDataProviderException::class);
        $this->expectExceptionMessage('The key "foo" has already been defined in the data provider "dataProvider".');

        Test::getProvidedData(\DuplicateKeyDataProviderTest::class, 'test');
    }

    public function testTestWithEmptyAnnotation(): void
    {
        $result = Test::getDataFromTestWithAnnotation("/**\n * @anotherAnnotation\n */");
        $this->assertNull($result);
    }

    public function testTestWithSimpleCase(): void
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith [1]
                                                                     */');
        $this->assertEquals([[1]], $result);
    }

    public function testTestWithMultiLineMultiParameterCase(): void
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith [1, 2]
                                                                     * [3, 4]
                                                                     */');
        $this->assertEquals([[1, 2], [3, 4]], $result);
    }

    public function testTestWithVariousTypes(): void
    {
        $result = Test::getDataFromTestWithAnnotation('/**
            * @testWith ["ab"]
            *           [true]
            *           [null]
         */');
        $this->assertEquals([['ab'], [true], [null]], $result);
    }

    public function testTestWithAnnotationAfter(): void
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith [1]
                                                                     *           [2]
                                                                     * @annotation
                                                                     */');
        $this->assertEquals([[1], [2]], $result);
    }

    public function testTestWithSimpleTextAfter(): void
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith [1]
                                                                     *           [2]
                                                                     * blah blah
                                                                     */');
        $this->assertEquals([[1], [2]], $result);
    }

    public function testTestWithCharacterEscape(): void
    {
        $result = Test::getDataFromTestWithAnnotation('/**
                                                                     * @testWith ["\"", "\""]
                                                                     */');
        $this->assertEquals([['"', '"']], $result);
    }

    public function testTestWithThrowsProperExceptionIfDatasetCannotBeParsed(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageRegExp('/^The data set for the @testWith annotation cannot be parsed:/');

        Test::getDataFromTestWithAnnotation('/**
                                                           * @testWith [s]
                                                           */');
    }

    public function testTestWithThrowsProperExceptionIfMultiLineDatasetCannotBeParsed(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageRegExp('/^The data set for the @testWith annotation cannot be parsed:/');

        Test::getDataFromTestWithAnnotation('/**
                                                           * @testWith ["valid"]
                                                           *           [invalid]
                                                           */');
    }

    /**
     * @todo Not sure what this test tests (name is misleading at least)
     */
    public function testParseAnnotation(): void
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
    public function methodForTestParseAnnotation(): void
    {
    }

    public function testParseAnnotationThatIsOnlyOneLine(): void
    {
        $this->assertEquals(
            ['Bar'],
            Test::getDependencies(\get_class($this), 'methodForTestParseAnnotationThatIsOnlyOneLine')
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
     * @throws CodeCoverageException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetLinesToBeCovered($test, $lines): void
    {
        if (\strpos($test, 'Namespace') === 0) {
            $expected = [
                TEST_FILES_PATH . 'NamespaceCoveredClass.php' => $lines,
            ];
        } elseif ($test === 'CoverageCoversOverridesCoversNothingTest') {
            $expected = [TEST_FILES_PATH . 'CoveredClass.php' => $lines];
        } elseif ($test === 'CoverageNoneTest') {
            $expected = [];
        } elseif ($test === 'CoverageNothingTest') {
            $expected = false;
        } elseif ($test === 'CoverageFunctionTest') {
            $expected = [
                TEST_FILES_PATH . 'CoveredFunction.php' => $lines,
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

    public function testGetLinesToBeCovered2(): void
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            'NotExistingCoveredElementTest',
            'testOne'
        );
    }

    public function testGetLinesToBeCovered3(): void
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            'NotExistingCoveredElementTest',
            'testTwo'
        );
    }

    public function testGetLinesToBeCovered4(): void
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            'NotExistingCoveredElementTest',
            'testThree'
        );
    }

    public function testGetLinesToBeCoveredSkipsNonExistentMethods(): void
    {
        $this->assertSame(
            [],
            Test::getLinesToBeCovered(
                'NotExistingCoveredElementTest',
                'methodDoesNotExist'
            )
        );
    }

    public function testTwoCoversDefaultClassAnnotationsAreNotAllowed(): void
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            'CoverageTwoDefaultClassAnnotations',
            'testSomething'
        );
    }

    public function testFunctionParenthesesAreAllowed(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => \range(10, 12)],
            Test::getLinesToBeCovered(
                'CoverageFunctionParenthesesTest',
                'testSomething'
            )
        );
    }

    public function testFunctionParenthesesAreAllowedWithWhitespace(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => \range(10, 12)],
            Test::getLinesToBeCovered(
                'CoverageFunctionParenthesesWhitespaceTest',
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowed(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => \range(29, 33)],
            Test::getLinesToBeCovered(
                'CoverageMethodParenthesesTest',
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowedWithWhitespace(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => \range(29, 33)],
            Test::getLinesToBeCovered(
                'CoverageMethodParenthesesWhitespaceTest',
                'testSomething'
            )
        );
    }

    public function testNamespacedFunctionCanBeCoveredOrUsed(): void
    {
        $this->assertEquals(
            [
                TEST_FILES_PATH . 'NamespaceCoveredFunction.php' => \range(12, 15),
            ],
            Test::getLinesToBeCovered(
                \CoverageNamespacedFunctionTest::class,
                'testFunc'
            )
        );
    }

    public function getLinesToBeCoveredProvider(): array
    {
        return [
            [
                'CoverageNoneTest',
                [],
            ],
            [
                'CoverageClassExtendedTest',
                \array_merge(\range(27, 44), \range(10, 25)),
            ],
            [
                'CoverageClassTest',
                \range(27, 44),
            ],
            [
                'CoverageMethodTest',
                \range(29, 33),
            ],
            [
                'CoverageMethodOneLineAnnotationTest',
                \range(29, 33),
            ],
            [
                'CoverageNotPrivateTest',
                \array_merge(\range(29, 33), \range(35, 39)),
            ],
            [
                'CoverageNotProtectedTest',
                \array_merge(\range(29, 33), \range(41, 43)),
            ],
            [
                'CoverageNotPublicTest',
                \array_merge(\range(35, 39), \range(41, 43)),
            ],
            [
                'CoveragePrivateTest',
                \range(41, 43),
            ],
            [
                'CoverageProtectedTest',
                \range(35, 39),
            ],
            [
                'CoveragePublicTest',
                \range(29, 33),
            ],
            [
                'CoverageFunctionTest',
                \range(10, 12),
            ],
            [
                'NamespaceCoverageClassExtendedTest',
                \array_merge(\range(29, 46), \range(12, 27)),
            ],
            [
                'NamespaceCoverageClassTest',
                \range(29, 46),
            ],
            [
                'NamespaceCoverageMethodTest',
                \range(31, 35),
            ],
            [
                'NamespaceCoverageNotPrivateTest',
                \array_merge(\range(31, 35), \range(37, 41)),
            ],
            [
                'NamespaceCoverageNotProtectedTest',
                \array_merge(\range(31, 35), \range(43, 45)),
            ],
            [
                'NamespaceCoverageNotPublicTest',
                \array_merge(\range(37, 41), \range(43, 45)),
            ],
            [
                'NamespaceCoveragePrivateTest',
                \range(43, 45),
            ],
            [
                'NamespaceCoverageProtectedTest',
                \range(37, 41),
            ],
            [
                'NamespaceCoveragePublicTest',
                \range(31, 35),
            ],
            [
                'NamespaceCoverageCoversClassTest',
                \array_merge(\range(43, 45), \range(37, 41), \range(31, 35), \range(24, 26), \range(19, 22), \range(14, 17)),
            ],
            [
                'NamespaceCoverageCoversClassPublicTest',
                \range(31, 35),
            ],
            [
                'CoverageNothingTest',
                false,
            ],
            [
                'CoverageCoversOverridesCoversNothingTest',
                \range(29, 33),
            ],
        ];
    }

    public function testParseTestMethodAnnotationsIncorporatesTraits(): void
    {
        $result = Test::parseTestMethodAnnotations(\ParseTestMethodAnnotationsMock::class);

        $this->assertArrayHasKey('class', $result);
        $this->assertArrayHasKey('method', $result);
        $this->assertArrayHasKey('theClassAnnotation', $result['class']);
        $this->assertArrayHasKey('theTraitAnnotation', $result['class']);
    }

    public function testCoversAnnotationIncludesTraitsUsedByClass(): void
    {
        $this->assertSame(
            [
                TEST_FILES_PATH . '3194.php' => \array_merge(\range(21, 29), \range(13, 19)),
            ],
            Test::getLinesToBeCovered(
                \Test3194::class,
                'testOne'
            )
        );
    }

    private function getRequirementsTestClassFile(): string
    {
        if (!$this->fileRequirementsTest) {
            $reflector                  = new \ReflectionClass(\RequirementsTest::class);
            $this->fileRequirementsTest = \realpath($reflector->getFileName());
        }

        return $this->fileRequirementsTest;
    }
}
