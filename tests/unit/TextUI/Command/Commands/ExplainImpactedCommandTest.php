<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestImpactAnalysis\ExplainedTest;
use PHPUnit\Runner\TestImpactAnalysis\Explanation;
use PHPUnit\Runner\TestImpactAnalysis\Provenance;
use PHPUnit\Runner\TestImpactAnalysis\SelectionReason;

#[CoversClass(ExplainImpactedCommand::class)]
#[UsesClass(ExplainedTest::class)]
#[UsesClass(Explanation::class)]
#[Small]
#[Group('textui')]
#[Group('textui/commands')]
final class ExplainImpactedCommandTest extends TestCase
{
    public function testReportsWhyEachTestThatCanBeAffectedByWhatChangedIsRun(): void
    {
        $result = new ExplainImpactedCommand(
            Explanation::of(
                [
                    'FooTest::testOne' => ExplainedTest::from(
                        'FooTest::testOne',
                        SelectionReason::DependsOnSomethingThatChanged,
                        '/src/Foo.php',
                    ),
                    'FooTest::testTwo' => ExplainedTest::from(
                        'FooTest::testTwo',
                        SelectionReason::DependsOnSomethingThatChanged,
                        '/src/Foo.php',
                    ),
                    'BarTest::testOne' => ExplainedTest::from(
                        'BarTest::testOne',
                        SelectionReason::NothingIsKnownAboutIt,
                    ),
                ],
                10,
            ),
            Provenance::ObservedExecution,
        )->execute();

        $this->assertSame(Result::SUCCESS, $result->shellExitCode());

        $this->assertSame(
            'Recorded from what the tests executed.' . PHP_EOL .
            PHP_EOL .
            '3 of 10 tests can be affected by what changed.' . PHP_EOL .
            PHP_EOL .
            '2 tests depend on something that changed:' . PHP_EOL .
            ' - FooTest::testOne' . PHP_EOL .
            '   /src/Foo.php' . PHP_EOL .
            ' - FooTest::testTwo' . PHP_EOL .
            '   /src/Foo.php' . PHP_EOL .
            PHP_EOL .
            '1 test has never been recorded:' . PHP_EOL .
            ' - BarTest::testOne' . PHP_EOL .
            PHP_EOL,
            $result->output(),
        );
    }

    public function testReportsWhatEachOfTheOtherReasonsIs(): void
    {
        $result = new ExplainImpactedCommand(
            Explanation::of(
                [
                    'FooTest::testOne' => ExplainedTest::from('FooTest::testOne', SelectionReason::ItDidNotPass),
                    'FooTest::testTwo' => ExplainedTest::from('FooTest::testTwo', SelectionReason::ItDidNotPass),
                    'BarTest::testOne' => ExplainedTest::from('BarTest::testOne', SelectionReason::AnotherTestDependsOnIt),
                    'BarTest::testTwo' => ExplainedTest::from('BarTest::testTwo', SelectionReason::AnotherTestDependsOnIt),
                    'a-phpt-test'      => ExplainedTest::from('a-phpt-test', SelectionReason::ItCannotBeRecorded),
                    'another-one'      => ExplainedTest::from('another-one', SelectionReason::ItCannotBeRecorded),
                ],
                6,
            ),
            Provenance::ObservedExecution,
        )->execute();

        $this->assertStringContainsString('2 tests did not pass when they were last run:', $result->output());
        $this->assertStringContainsString('2 tests are depended upon by another test that is run:', $result->output());
        $this->assertStringContainsString('2 tests are not test methods and can never be recorded:', $result->output());
    }

    public function testUsesTheSingularForASingleTest(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::of(
                [
                    'FooTest::testOne' => ExplainedTest::from('FooTest::testOne', SelectionReason::DependsOnSomethingThatChanged, '/src/Foo.php'),
                    'FooTest::testTwo' => ExplainedTest::from('FooTest::testTwo', SelectionReason::ItDidNotPass),
                    'BarTest::testOne' => ExplainedTest::from('BarTest::testOne', SelectionReason::AnotherTestDependsOnIt),
                    'a-phpt-test'      => ExplainedTest::from('a-phpt-test', SelectionReason::ItCannotBeRecorded),
                    'BazTest::testOne' => ExplainedTest::from('BazTest::testOne', SelectionReason::NothingIsKnownAboutIt),
                ],
                5,
            ),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertStringContainsString('1 test depends on something that changed:', $output);
        $this->assertStringContainsString('1 test has never been recorded:', $output);
        $this->assertStringContainsString('1 test did not pass when it was last run:', $output);
        $this->assertStringContainsString('1 test is depended upon by another test that is run:', $output);
        $this->assertStringContainsString('1 test is not a test method and can never be recorded:', $output);
    }

    public function testSaysWhereWhatIsReportedComesFromWhenItWasDerivedFromCodeCoverageTargets(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::of(
                ['FooTest::testOne' => ExplainedTest::from('FooTest::testOne', SelectionReason::NothingIsKnownAboutIt)],
                1,
            ),
            Provenance::CoverageTargets,
        )->execute()->output();

        $this->assertStringStartsWith('Recorded from the code coverage targets the tests declare.', $output);
    }

    public function testSaysWhyEveryTestIsRunWhenNoTestCanBeLeftOut(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::everything('no test impact data has been recorded'),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertSame(
            'Every test is run: no test impact data has been recorded' . PHP_EOL,
            $output,
        );
    }

    public function testSaysThatNoTestCanBeAffectedByWhatChanged(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::of([], 10),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertSame(
            'Recorded from what the tests executed.' . PHP_EOL .
            PHP_EOL .
            '0 of 10 tests can be affected by what changed' . PHP_EOL,
            $output,
        );
    }
}
