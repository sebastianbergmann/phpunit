<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use function array_merge;
use function get_class;
use function range;
use PHPUnit\Framework\TestCase;
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
use PHPUnit\TestFixture\NamespaceCoverageClassTest;
use PHPUnit\TestFixture\NamespaceCoverageCoversClassPublicTest;
use PHPUnit\TestFixture\NamespaceCoverageCoversClassTest;
use PHPUnit\TestFixture\NamespaceCoverageMethodTest;
use PHPUnit\TestFixture\Test3194;

/**
 * @small
 */
final class CodeCoverageFacadeTest extends TestCase
{
    /**
     * @dataProvider getLinesToBeCoveredProvider
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
            (new CodeCoverageFacade)->linesToBeCovered(
                $test,
                'testSomething'
            )
        );
    }

    public function testFunctionParenthesesAreAllowed(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => range(10, 12)],
            (new CodeCoverageFacade)->linesToBeCovered(
                CoverageFunctionParenthesesTest::class,
                'testSomething'
            )
        );
    }

    public function testFunctionParenthesesAreAllowedWithWhitespace(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => range(10, 12)],
            (new CodeCoverageFacade)->linesToBeCovered(
                CoverageFunctionParenthesesWhitespaceTest::class,
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowed(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => range(31, 35)],
            (new CodeCoverageFacade)->linesToBeCovered(
                CoverageMethodParenthesesTest::class,
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowedWithWhitespace(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => range(31, 35)],
            (new CodeCoverageFacade)->linesToBeCovered(
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
            (new CodeCoverageFacade)->linesToBeCovered(
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

    public function testCoversAnnotationIncludesTraitsUsedByClass(): void
    {
        $this->assertSame(
            [
                TEST_FILES_PATH . '3194.php' => array_merge(range(14, 20), range(22, 30)),
            ],
            (new CodeCoverageFacade)->linesToBeCovered(
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
        $coverageRequired = (new CodeCoverageFacade)->shouldCodeCoverageBeCollectedFor(get_class($test), $test->getName(false));
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
}
