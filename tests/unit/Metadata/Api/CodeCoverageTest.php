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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\CoversClassOnClassTest;
use PHPUnit\TestFixture\CoversNothingOnClassTest;
use PHPUnit\TestFixture\CoversNothingOnMethodTest;
use PHPUnit\TestFixture\NoCoverageAttributesTest;

#[CoversClass(CodeCoverage::class)]
#[Small]
#[Group('metadata')]
final class CodeCoverageTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: bool, 1: class-string}>
     */
    public static function canSkipCoverageProvider(): array
    {
        return [
            [false, NoCoverageAttributesTest::class],
            [false, CoversClassOnClassTest::class],
            [true, CoversNothingOnClassTest::class],
            [true, CoversNothingOnMethodTest::class],
        ];
    }

    /**
     * @param class-string $testCase
     */
    #[DataProvider('canSkipCoverageProvider')]
    public function testWhetherCollectionOfCodeCoverageDataCanBeSkippedCanBeDetermined(bool $expected, string $testCase): void
    {
        $test             = new $testCase('testSomething');
        $coverageRequired = (new CodeCoverage)->shouldCodeCoverageBeCollectedFor($test::class, $test->name());
        $canSkipCoverage  = !$coverageRequired;

        $this->assertSame($expected, $canSkipCoverage);
    }
}
