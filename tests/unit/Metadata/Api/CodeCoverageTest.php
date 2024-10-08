<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use function array_merge;
use function range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\CoverageClassTest;
use PHPUnit\TestFixture\CoverageClassWithoutAnnotationsTest;
use PHPUnit\TestFixture\CoverageFunctionTest;
use PHPUnit\TestFixture\CoverageMethodNothingTest;
use PHPUnit\TestFixture\CoverageMethodTest;
use PHPUnit\TestFixture\CoverageNoneTest;
use PHPUnit\TestFixture\CoveredClassUsingCoveredTraitTest;
use PHPUnit\TestFixture\InterfaceAsTargetWithAttributeTest;
use PHPUnit\TestFixture\InvalidClassTargetWithAttributeTest;
use PHPUnit\TestFixture\InvalidFunctionTargetTest;
use PHPUnit\TestFixture\Test3194;

#[CoversClass(CodeCoverage::class)]
#[Small]
#[Group('metadata')]
final class CodeCoverageTest extends TestCase
{
    public static function linesToBeCoveredProvider(): array
    {
        return [
            [
                [],
                CoverageNoneTest::class,
                'testSomething',
            ],

            [
                [
                    TEST_FILES_PATH . 'CoveredClass.php'       => array_merge(range(12, 29)),
                    TEST_FILES_PATH . 'CoveredParentClass.php' => array_merge(range(12, 27)),
                ],
                CoverageClassTest::class,
                'testSomething',
            ],

            [
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => range(14, 18),
                ],
                CoverageMethodTest::class,
                'testSomething',
            ],

            [
                [
                    TEST_FILES_PATH . 'CoveredClassUsingCoveredTrait.php' => range(12, 27),
                    TEST_FILES_PATH . 'CoveredTrait.php'                  => range(12, 18),
                ],
                CoveredClassUsingCoveredTraitTest::class,
                'testSomething',
            ],

            [
                false,
                CoverageMethodNothingTest::class,
                'testSomething',
            ],

            [
                [
                    TEST_FILES_PATH . 'CoveredFunction.php' => range(10, 12),
                ],
                CoverageFunctionTest::class,
                'testSomething',
            ],

            [
                [
                    TEST_FILES_PATH . '3194.php' => array_merge(range(15, 21), range(23, 31)),
                ],
                Test3194::class,
                'testOne',
            ],
        ];
    }

    public static function linesToBeUsedProvider(): array
    {
        return [
            [
                [],
                CoverageNoneTest::class,
                'testSomething',
            ],

            [
                [
                    TEST_FILES_PATH . 'CoveredClass.php'       => array_merge(range(12, 29)),
                    TEST_FILES_PATH . 'CoveredParentClass.php' => array_merge(range(12, 27)),
                ],
                CoverageClassTest::class,
                'testSomething',
            ],

            [
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => range(14, 18),
                ],
                CoverageMethodTest::class,
                'testSomething',
            ],

            [
                [
                    TEST_FILES_PATH . 'CoveredClassUsingCoveredTrait.php' => range(12, 27),
                    TEST_FILES_PATH . 'CoveredTrait.php'                  => range(12, 18),
                ],
                CoveredClassUsingCoveredTraitTest::class,
                'testSomething',
            ],

            [
                [
                    TEST_FILES_PATH . 'CoveredFunction.php' => range(10, 12),
                ],
                CoverageFunctionTest::class,
                'testSomething',
            ],
        ];
    }

    public static function canSkipCoverageProvider(): array
    {
        return [
            [CoverageClassTest::class, false],
            [CoverageClassWithoutAnnotationsTest::class, false],
            [CoverageMethodNothingTest::class, true],
        ];
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('linesToBeCoveredProvider')]
    public function testLinesToBeCoveredCanBeDetermined(array|false $expected, string $className, string $methodName): void
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            (new CodeCoverage)->linesToBeCovered(
                $className,
                $methodName,
            ),
        );
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('linesToBeUsedProvider')]
    public function testLinesToBeUsedCanBeDetermined(array|false $expected, string $className, string $methodName): void
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            (new CodeCoverage)->linesToBeUsed(
                $className,
                $methodName,
            ),
        );
    }

    /**
     * @param class-string $testCase
     */
    #[DataProvider('canSkipCoverageProvider')]
    public function testWhetherCollectionOfCodeCoverageDataCanBeSkippedCanBeDetermined(string $testCase, bool $expectedCanSkip): void
    {
        $test             = new $testCase('testSomething');
        $coverageRequired = (new CodeCoverage)->shouldCodeCoverageBeCollectedFor($test::class, $test->name());
        $canSkipCoverage  = !$coverageRequired;

        $this->assertEquals($expectedCanSkip, $canSkipCoverage);
    }

    public function testRejectsInterfaceAsCoversClassTargetWithAttribute(): void
    {
        $this->expectException(CodeCoverageException::class);
        $this->expectExceptionMessage('Interface "Throwable" is not a valid target for code coverage');

        (new CodeCoverage)->linesToBeCovered(InterfaceAsTargetWithAttributeTest::class, 'testOne');
    }

    public function testRejectsInterfaceAsUsesClassTargetWithAttribute(): void
    {
        $this->expectException(CodeCoverageException::class);
        $this->expectExceptionMessage('Interface "Throwable" is not a valid target for code coverage');

        (new CodeCoverage)->linesToBeUsed(InterfaceAsTargetWithAttributeTest::class, 'testOne');
    }

    public function testRejectsInvalidCoversClassTargetWithAttribute(): void
    {
        $this->expectException(CodeCoverageException::class);
        $this->expectExceptionMessage('"InvalidClass" is not a valid target for code coverage');

        (new CodeCoverage)->linesToBeCovered(InvalidClassTargetWithAttributeTest::class, 'testOne');
    }

    public function testRejectsInvalidUsesClassTargetWithAttribute(): void
    {
        $this->expectException(CodeCoverageException::class);
        $this->expectExceptionMessage('"InvalidClass" is not a valid target for code coverage');

        (new CodeCoverage)->linesToBeUsed(InvalidClassTargetWithAttributeTest::class, 'testOne');
    }

    public function testRejectsInvalidCoversFunctionTarget(): void
    {
        $this->expectException(CodeCoverageException::class);
        $this->expectExceptionMessage('::invalid_function is not a valid target for code coverage');

        (new CodeCoverage)->linesToBeCovered(InvalidFunctionTargetTest::class, 'testOne');
    }

    public function testRejectsInvalidUsesFunctionTarget(): void
    {
        $this->expectException(CodeCoverageException::class);
        $this->expectExceptionMessage('::invalid_function is not a valid target for code coverage');

        (new CodeCoverage)->linesToBeUsed(InvalidFunctionTargetTest::class, 'testOne');
    }
}
