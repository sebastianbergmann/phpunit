<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output;

use PHPUnit\Event\Code\IssueTrigger\Code;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\DataFromDataProvider;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestRunner\TestResult\Issues\Issue;

#[CoversClass(TriggeringTestsSummary::class)]
#[Small]
#[Group('textui')]
final class TriggeringTestsSummaryTest extends TestCase
{
    public function testListsTheTestThatTriggeredTheIssue(): void
    {
        $summary = TriggeringTestsSummary::from(
            Issue::from('file.php', 1, 'description', $this->testMethod('FooTest', 'testOne')),
            5,
        );

        $this->assertSame('Triggered 1 time by 1 test', $summary->description());
        $this->assertSame(['FooTest::testOne'], $summary->entries());
    }

    public function testCountsHowOftenTheSameTestTriggeredTheIssue(): void
    {
        $issue = Issue::from('file.php', 1, 'description', $this->testMethod('FooTest', 'testOne'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testOne'));

        $summary = TriggeringTestsSummary::from($issue, 5);

        $this->assertSame('Triggered 2 times by 1 test', $summary->description());
        $this->assertSame(['FooTest::testOne (2 times)'], $summary->entries());
    }

    public function testGroupsDataSetsByTestMethod(): void
    {
        $issue = Issue::from('file.php', 1, 'description', $this->testMethod('FooTest', 'testOne', 'one'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testOne', 'two'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testOne', 'two'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testOne', 'three'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo', 'one'));

        $summary = TriggeringTestsSummary::from($issue, 5);

        $this->assertSame('Triggered 5 times by 4 tests', $summary->description());
        $this->assertSame(
            [
                'FooTest::testOne (3 data sets, 4 times)',
                'FooTest::testTwo#one',
            ],
            $summary->entries(),
        );
    }

    public function testListsTheTestsThatTriggeredTheIssueMostOftenFirst(): void
    {
        $issue = Issue::from('file.php', 1, 'description', $this->testMethod('FooTest', 'testOne'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testThree'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo'));

        $summary = TriggeringTestsSummary::from($issue, 5);

        $this->assertSame(
            [
                'FooTest::testTwo (2 times)',
                'FooTest::testOne',
                'FooTest::testThree',
            ],
            $summary->entries(),
        );
    }

    public function testListsAtMostTheGivenNumberOfTests(): void
    {
        $issue = Issue::from('file.php', 1, 'description', $this->testMethod('FooTest', 'testOne'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testThree', 'one'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testThree', 'two'));

        $summary = TriggeringTestsSummary::from($issue, 1);

        $this->assertSame('Triggered 4 times by 4 tests', $summary->description());
        $this->assertSame(
            [
                'FooTest::testThree (2 data sets)',
                '... 2 more tests',
            ],
            $summary->entries(),
        );
    }

    public function testGroupsTestsByTestClassWhenThereAreMoreTestMethodsThanTheGivenNumber(): void
    {
        $issue = Issue::from('file.php', 1, 'description', $this->testMethod('FooTest', 'testOne'));
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo'));
        $issue->triggeredBy($this->testMethod('BarTest', 'testOne'));
        $issue->triggeredBy($this->testMethod('BarTest', 'testTwo'));
        $issue->triggeredBy($this->testMethod('BarTest', 'testThree'));
        $issue->triggeredBy($this->testMethod('BazTest', 'testOne', 'one'));
        $issue->triggeredBy($this->testMethod('BazTest', 'testOne', 'two'));
        $issue->triggeredBy($this->testMethod('QuxTest', 'testOne'));

        $summary = TriggeringTestsSummary::from($issue, 3);

        $this->assertSame('Triggered 8 times by 8 tests in 4 test classes', $summary->description());
        $this->assertSame(
            [
                'BarTest (3 tests)',
                'BazTest::testOne (2 data sets)',
                'FooTest (2 tests)',
                '... 1 more test class',
            ],
            $summary->entries(),
        );
    }

    public function testDoesNotGroupTestsByTestClassWhenThisDoesNotReduceTheNumberOfEntries(): void
    {
        $issue = Issue::from('file.php', 1, 'description', $this->testMethod('FooTest', 'testOne'));
        $issue->triggeredBy($this->testMethod('BarTest', 'testOne'));
        $issue->triggeredBy($this->testMethod('BazTest', 'testOne'));

        $summary = TriggeringTestsSummary::from($issue, 2);

        $this->assertSame('Triggered 3 times by 3 tests', $summary->description());
        $this->assertSame(
            [
                'BarTest::testOne',
                'BazTest::testOne',
                '... 1 more test',
            ],
            $summary->entries(),
        );
    }

    public function testGroupsRepetitionsByTestMethod(): void
    {
        $issue = Issue::from('file.php', 1, 'description', $this->testMethod('FooTest', 'testOne', null, 1, 3));
        $issue->triggeredBy($this->testMethod('FooTest', 'testOne', null, 2, 3));
        $issue->triggeredBy($this->testMethod('FooTest', 'testOne', null, 3, 3));
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo', 'one', 1, 2));
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo', 'one', 2, 2));
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo', 'two', 1, 2));
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo', 'two', 2, 2));

        $summary = TriggeringTestsSummary::from($issue, 5);

        $this->assertSame('Triggered 7 times by 7 tests', $summary->description());
        $this->assertSame(
            [
                'FooTest::testTwo (2 data sets, 4 tests)',
                'FooTest::testOne (3 tests)',
            ],
            $summary->entries(),
        );
    }

    public function testListsPhptTests(): void
    {
        $issue = Issue::from('file.php', 1, 'description', new Phpt('one.phpt'));
        $issue->triggeredBy(new Phpt('two.phpt'));

        $summary = TriggeringTestsSummary::from($issue, 5);

        $this->assertSame(['one.phpt', 'two.phpt'], $summary->entries());
    }

    public function testDescribesHowTheIssueWasTriggered(): void
    {
        $direct = IssueTrigger::from(Code::ThirdParty, Code::FirstParty);

        $issue = Issue::from('file.php', 1, 'description', $this->testMethod('FooTest', 'testOne'), null, $direct);
        $issue->triggeredBy($this->testMethod('FooTest', 'testTwo'), $direct);

        $this->assertSame('Triggered 2 times (direct) by 2 tests', TriggeringTestsSummary::from($issue, 5)->description());

        $issue->triggeredBy($this->testMethod('FooTest', 'testThree'), IssueTrigger::from(Code::ThirdParty, Code::ThirdParty));
        $issue->triggeredBy($this->testMethod('FooTest', 'testFour'), IssueTrigger::from(Code::FirstParty, Code::FirstParty));

        $this->assertSame('Triggered 4 times (1 self, 2 direct, 1 indirect) by 4 tests', TriggeringTestsSummary::from($issue, 5)->description());
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     * @param positive-int     $repetition
     * @param positive-int     $totalRepetitions
     */
    private function testMethod(string $className, string $methodName, ?string $dataSetName = null, int $repetition = 1, int $totalRepetitions = 1): TestMethod
    {
        $testData = [];

        if ($dataSetName !== null) {
            $testData[] = DataFromDataProvider::from($dataSetName, '', '');
        }

        return new TestMethod(
            $className,
            $methodName,
            $className . '.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName($className, $methodName),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray($testData),
            $repetition,
            $totalRepetitions,
        );
    }
}
