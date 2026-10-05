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
use PHPUnit\Runner\TestImpactAnalysis\RecordingTime;
use PHPUnit\Runner\TestImpactAnalysis\SelectionReason;

#[CoversClass(ExplainImpactedCommand::class)]
#[UsesClass(ExplainedTest::class)]
#[UsesClass(Explanation::class)]
#[UsesClass(RecordingTime::class)]
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
                $this->recordedAt(),
                [],
            ),
            Provenance::ObservedExecution,
        )->execute();

        $this->assertSame(Result::SUCCESS, $result->shellExitCode());

        $this->assertSame(
            'Recorded at ' . $this->recordedAt()->asString() . ' from what the tests executed and, for a test that ran in a process of its own, what that process loaded.' . PHP_EOL .
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
        $output = new ExplainImpactedCommand(
            Explanation::of(
                [
                    'FooTest::testOne' => ExplainedTest::from('FooTest::testOne', SelectionReason::ItDidNotPass),
                    'FooTest::testTwo' => ExplainedTest::from('FooTest::testTwo', SelectionReason::ItDidNotPass),
                    'BarTest::testOne' => ExplainedTest::from('BarTest::testOne', SelectionReason::AnotherTestDependsOnIt),
                    'BarTest::testTwo' => ExplainedTest::from('BarTest::testTwo', SelectionReason::AnotherTestDependsOnIt),
                    'a-phpt-test'      => ExplainedTest::from('a-phpt-test', SelectionReason::ItCannotBeRecorded),
                    'another-one'      => ExplainedTest::from('another-one', SelectionReason::ItCannotBeRecorded),
                    'BazTest::testOne' => ExplainedTest::from('BazTest::testOne', SelectionReason::DependsOnATestThatCanBeAffected),
                    'BazTest::testTwo' => ExplainedTest::from('BazTest::testTwo', SelectionReason::DependsOnATestThatCanBeAffected),
                ],
                8,
                $this->recordedAt(),
                [],
            ),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertStringContainsString('2 tests did not pass when they were last run:', $output);
        $this->assertStringContainsString('2 tests are depended upon by another test that is run:', $output);
        $this->assertStringContainsString('2 tests are not test methods and can never be recorded:', $output);
        $this->assertStringContainsString('2 tests depend on a test that can be affected by what changed:', $output);
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
                    'QuxTest::testOne' => ExplainedTest::from('QuxTest::testOne', SelectionReason::DependsOnATestThatCanBeAffected),
                ],
                6,
                $this->recordedAt(),
                [],
            ),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertStringContainsString('1 test depends on something that changed:', $output);
        $this->assertStringContainsString('1 test has never been recorded:', $output);
        $this->assertStringContainsString('1 test did not pass when it was last run:', $output);
        $this->assertStringContainsString('1 test is depended upon by another test that is run:', $output);
        $this->assertStringContainsString('1 test is not a test method and can never be recorded:', $output);
        $this->assertStringContainsString('1 test depends on a test that can be affected by what changed:', $output);
    }

    /**
     * A test that depends on a test that can be affected by what changed is
     * run because of what changed as well, and is reported right after the
     * tests that depend on something that changed.
     */
    public function testReportsTheTestsThatDependOnATestThatCanBeAffectedRightAfterTheTestsThatDependOnSomethingThatChanged(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::of(
                [
                    'BarTest::testOne' => ExplainedTest::from('BarTest::testOne', SelectionReason::NothingIsKnownAboutIt),
                    'FooTest::testTwo' => ExplainedTest::from('FooTest::testTwo', SelectionReason::DependsOnATestThatCanBeAffected),
                    'FooTest::testOne' => ExplainedTest::from('FooTest::testOne', SelectionReason::DependsOnSomethingThatChanged, '/src/Foo.php'),
                ],
                3,
                $this->recordedAt(),
                [],
            ),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertStringEndsWith(
            '1 test depends on something that changed:' . PHP_EOL .
            ' - FooTest::testOne' . PHP_EOL .
            '   /src/Foo.php' . PHP_EOL .
            PHP_EOL .
            '1 test depends on a test that can be affected by what changed:' . PHP_EOL .
            ' - FooTest::testTwo' . PHP_EOL .
            PHP_EOL .
            '1 test has never been recorded:' . PHP_EOL .
            ' - BarTest::testOne' . PHP_EOL .
            PHP_EOL,
            $output,
        );
    }

    public function testSaysWhereWhatIsReportedComesFromWhenItWasDerivedFromCodeCoverageTargets(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::of(
                ['FooTest::testOne' => ExplainedTest::from('FooTest::testOne', SelectionReason::NothingIsKnownAboutIt)],
                1,
                $this->recordedAt(),
                [],
            ),
            Provenance::CoverageTargets,
        )->execute()->output();

        $this->assertStringStartsWith(
            'Recorded at ' . $this->recordedAt()->asString() . ' from the code coverage targets the tests declare.',
            $output,
        );
    }

    public function testSaysWhyEveryTestIsRunWhenNothingWasRecorded(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::everything('no test impact data has been recorded', null),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertSame(
            'Every test is run: no test impact data has been recorded' . PHP_EOL,
            $output,
        );
    }

    public function testSaysWhenWhatIsKnownWasRecordedWhenEveryTestIsRunAllTheSame(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::everything('/src/Foo.php changed and no test is recorded as depending on it', $this->recordedAt()),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertSame(
            'Recorded at ' . $this->recordedAt()->asString() . ' from what the tests executed and, for a test that ran in a process of its own, what that process loaded.' . PHP_EOL .
            PHP_EOL .
            'Every test is run: /src/Foo.php changed and no test is recorded as depending on it' . PHP_EOL,
            $output,
        );
    }

    public function testSaysThatNoTestCanBeAffectedByWhatChanged(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::of([], 10, $this->recordedAt(), []),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertSame(
            'Recorded at ' . $this->recordedAt()->asString() . ' from what the tests executed and, for a test that ran in a process of its own, what that process loaded.' . PHP_EOL .
            PHP_EOL .
            '0 of 10 tests can be affected by what changed' . PHP_EOL,
            $output,
        );
    }

    public function testReportsTheFilesThatWereAddedWhereAddedFilesAreReachedThroughChanges(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::of(
                [
                    'FooTest::testOne' => ExplainedTest::from('FooTest::testOne', SelectionReason::DependsOnSomethingThatChanged, '/src/Foo.php'),
                ],
                10,
                $this->recordedAt(),
                ['/src/Jobs/SendInvoice.php', '/src/Services/Invoicing.php'],
            ),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertSame(
            'Recorded at ' . $this->recordedAt()->asString() . ' from what the tests executed and, for a test that ran in a process of its own, what that process loaded.' . PHP_EOL .
            PHP_EOL .
            '1 of 10 tests can be affected by what changed.' . PHP_EOL .
            PHP_EOL .
            '1 test depends on something that changed:' . PHP_EOL .
            ' - FooTest::testOne' . PHP_EOL .
            '   /src/Foo.php' . PHP_EOL .
            PHP_EOL .
            '2 files were added that can only affect a test through a file that was changed to use them, as configured in <addedFilesAreReachedThroughChanges>:' . PHP_EOL .
            ' - /src/Jobs/SendInvoice.php' . PHP_EOL .
            ' - /src/Services/Invoicing.php' . PHP_EOL .
            PHP_EOL,
            $output,
        );
    }

    public function testReportsTheFileThatWasAddedWhereAddedFilesAreReachedThroughChangesWhenNoTestCanBeAffected(): void
    {
        $output = new ExplainImpactedCommand(
            Explanation::of([], 10, $this->recordedAt(), ['/src/Jobs/SendInvoice.php']),
            Provenance::ObservedExecution,
        )->execute()->output();

        $this->assertSame(
            'Recorded at ' . $this->recordedAt()->asString() . ' from what the tests executed and, for a test that ran in a process of its own, what that process loaded.' . PHP_EOL .
            PHP_EOL .
            '0 of 10 tests can be affected by what changed.' . PHP_EOL .
            PHP_EOL .
            '1 file was added that can only affect a test through a file that was changed to use it, as configured in <addedFilesAreReachedThroughChanges>:' . PHP_EOL .
            ' - /src/Jobs/SendInvoice.php' . PHP_EOL .
            PHP_EOL,
            $output,
        );
    }

    private function recordedAt(): RecordingTime
    {
        return RecordingTime::fromUnixTimestamp(1700000000);
    }
}
