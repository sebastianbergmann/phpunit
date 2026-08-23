<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use function array_map;
use function basename;
use function realpath;
use function sort;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\TestFixture\TestImpactAnalysis\FormatterMethodTest;
use PHPUnit\TestFixture\TestImpactAnalysis\FormatterThatCoversNothingTest;
use PHPUnit\TestFixture\TestImpactAnalysis\InvoiceThatUsesMoneyTest;
use SebastianBergmann\CodeCoverage\Filter;

#[CoversClass(TestImpactDataFromCoverageTargets::class)]
#[UsesClass(DefaultTestImpactData::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class TestImpactDataFromCoverageTargetsTest extends TestCase
{
    public function testRecordsTheSourceFilesTheTargetsOfATestName(): void
    {
        $data = new DefaultTestImpactData;

        $this->coverageTargets()->record([new InvoiceThatUsesMoneyTest('testTotalIsTheSumOfItsItems')], $data);

        $this->assertSame(
            [InvoiceThatUsesMoneyTest::class . '::testTotalIsTheSumOfItsItems' => ['Invoice.php', 'Money.php']],
            $this->baseNames($data),
        );
    }

    public function testRecordsTheSourceFileOfATargetThatNamesAMethod(): void
    {
        $data = new DefaultTestImpactData;

        $this->coverageTargets()->record([new FormatterMethodTest('testFormatsAmount')], $data);

        $this->assertSame(
            [FormatterMethodTest::class . '::testFormatsAmount' => ['Formatter.php']],
            $this->baseNames($data),
        );
    }

    public function testDoesNotRecordATestThatDeclaresThatItCoversNothing(): void
    {
        $data = new DefaultTestImpactData;

        $this->coverageTargets()->record([new FormatterThatCoversNothingTest('testFormatsAmount')], $data);

        $this->assertSame([], $data->recorded());
    }

    public function testDoesNotRecordATestThatIsNotATestMethod(): void
    {
        $data = new DefaultTestImpactData;

        $this->coverageTargets()->record(
            [new PhptTestCase(realpath(__DIR__ . '/../../../_files/TestImpactAnalysis/test-that-declares-nothing.phpt'))],
            $data,
        );

        $this->assertSame([], $data->recorded());
    }

    private function coverageTargets(): TestImpactDataFromCoverageTargets
    {
        $filter = new Filter;

        $filter->includeFiles(
            [
                realpath(__DIR__ . '/../../../_files/TestImpactAnalysis/Invoice.php'),
                realpath(__DIR__ . '/../../../_files/TestImpactAnalysis/Money.php'),
                realpath(__DIR__ . '/../../../_files/TestImpactAnalysis/Formatter.php'),
            ],
        );

        return TestImpactDataFromCoverageTargets::using($filter, null, true, false);
    }

    /**
     * @return array<non-empty-string, list<non-empty-string>>
     */
    private function baseNames(DefaultTestImpactData $data): array
    {
        $recorded = [];

        foreach ($data->recorded() as $test => $files) {
            $files = array_map(static fn (string $file): string => basename($file), $files);

            sort($files);

            $recorded[$test] = $files;
        }

        return $recorded;
    }
}
