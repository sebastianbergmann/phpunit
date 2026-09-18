<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use function uniqid;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\BankAccountTest;
use PHPUnit\TextUI\CliArguments\Builder as CliBuilder;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use ReflectionProperty;

#[CoversClass(TestSuiteFilterProcessor::class)]
#[Medium]
#[Group('textui')]
final class TestSuiteFilterProcessorTest extends TestCase
{
    public function testRejectsTestIdFilterFileThatCannotBeRead(): void
    {
        $file = '/path/to/file/that/does/not/exist/' . uniqid('test_id_filter_file_');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read from ' . $file);

        (new TestSuiteFilterProcessor)->process(
            $this->configuration($file),
            TestSuite::empty('test suite'),
        );
    }

    public function testRunsOnlyTheTestsThatTestImpactAnalysisSelected(): void
    {
        $suite = $this->testSuite();

        $this->process($suite, ['PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative']);

        $this->assertSame(
            ['PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative'],
            $this->idsOfTestsIn($suite),
        );
    }

    /**
     * A selection that selected no test is not the same as no selection: it
     * says that no test can be affected by what changed, and no test is
     * therefore run.
     */
    public function testRunsNoTestWhenTestImpactAnalysisSelectedNone(): void
    {
        $suite = $this->testSuite();

        $this->process($suite, []);

        $this->assertSame([], $this->idsOfTestsIn($suite));
    }

    public function testRunsEveryTestWhenTestImpactAnalysisSelectedNothing(): void
    {
        $suite = $this->testSuite();

        $this->process($suite, null);

        $this->assertSame(
            [
                'PHPUnit\TestFixture\BankAccountTest::testBalanceIsInitiallyZero',
                'PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative',
            ],
            $this->idsOfTestsIn($suite),
        );
    }

    private function testSuite(): TestSuite
    {
        $suite = TestSuite::empty('test suite');

        $suite->addTest(new BankAccountTest('testBalanceIsInitiallyZero'));
        $suite->addTest(new BankAccountTest('testBalanceCannotBecomeNegative'));

        return $suite;
    }

    /**
     * @return list<non-empty-string>
     */
    private function idsOfTestsIn(TestSuite $suite): array
    {
        $ids = [];

        foreach ($suite->collect() as $test) {
            $ids[] = $test->valueObjectForEvents()->id();
        }

        return $ids;
    }

    /*
     * TestSuiteFilterProcessor emits a test runner event for the test suite it
     * filtered. This must not end up in the result of the test run that
     * exercises TestSuiteFilterProcessor, so it is emitted into a throw-away
     * event facade that is never forwarded.
     *
     * @param ?list<non-empty-string> $selectedTests
     */
    private function process(TestSuite $suite, ?array $selectedTests): void
    {
        $property = new ReflectionProperty(EventFacade::class, 'instance');
        $facade   = $property->getValue();

        $property->setValue(null, new EventFacade);

        try {
            (new TestSuiteFilterProcessor)->process(
                $this->configurationWithoutFilters(),
                $suite,
                $selectedTests,
            );
        } finally {
            $property->setValue(null, $facade);
        }
    }

    private function configuration(string $testIdFilterFile): Configuration
    {
        return (new Merger)->merge(
            (new CliBuilder)->fromParameters([
                '--test-id-filter-file',
                $testIdFilterFile,
            ]),
            DefaultConfiguration::create(),
        );
    }

    private function configurationWithoutFilters(): Configuration
    {
        return (new Merger)->merge(
            (new CliBuilder)->fromParameters([]),
            DefaultConfiguration::create(),
        );
    }
}
