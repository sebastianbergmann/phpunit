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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Explanation::class)]
#[UsesClass(ExplainedTest::class)]
#[UsesClass(RecordingTime::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class ExplanationTest extends TestCase
{
    public function testKnowsThatEveryTestIsRun(): void
    {
        $explanation = Explanation::everything('a reason', null);

        $this->assertTrue($explanation->isEverything());
        $this->assertSame('a reason', $explanation->reasonEverythingIsRun());
        $this->assertSame([], $explanation->testsThatAreRun());
        $this->assertSame([], $explanation->asArray());
        $this->assertSame([], $explanation->testsRunBecause(SelectionReason::ItDidNotPass));
        $this->assertSame(0, $explanation->numberOfTestsThatAreRun());
    }

    public function testKnowsWhichTestsAreRunAndWhy(): void
    {
        $changed = ExplainedTest::from('FooTest::testOne', SelectionReason::DependsOnSomethingThatChanged, '/src/Foo.php');
        $unknown = ExplainedTest::from('BarTest::testOne', SelectionReason::NothingIsKnownAboutIt);

        $explanation = Explanation::of(
            [
                'FooTest::testOne' => $changed,
                'BarTest::testOne' => $unknown,
            ],
            10,
            RecordingTime::fromUnixTimestamp(1700000000),
        );

        $this->assertFalse($explanation->isEverything());
        $this->assertSame(['FooTest::testOne', 'BarTest::testOne'], $explanation->testsThatAreRun());
        $this->assertSame([$changed, $unknown], $explanation->asArray());
        $this->assertSame(2, $explanation->numberOfTestsThatAreRun());
        $this->assertSame(10, $explanation->numberOfTestsThatWereConsidered());
    }

    public function testKnowsWhichTestsAreRunForOneReason(): void
    {
        $first  = ExplainedTest::from('FooTest::testOne', SelectionReason::DependsOnSomethingThatChanged, '/src/Foo.php');
        $second = ExplainedTest::from('FooTest::testTwo', SelectionReason::DependsOnSomethingThatChanged, '/src/Foo.php');

        $explanation = Explanation::of(
            [
                'FooTest::testOne' => $first,
                'BarTest::testOne' => ExplainedTest::from('BarTest::testOne', SelectionReason::NothingIsKnownAboutIt),
                'FooTest::testTwo' => $second,
            ],
            3,
            RecordingTime::fromUnixTimestamp(1700000000),
        );

        $this->assertSame(
            [$first, $second],
            $explanation->testsRunBecause(SelectionReason::DependsOnSomethingThatChanged),
        );

        $this->assertSame([], $explanation->testsRunBecause(SelectionReason::ItCannotBeRecorded));
    }

    public function testKnowsWhenWhatItWasMadeFromWasRecorded(): void
    {
        $recordedAt = RecordingTime::fromUnixTimestamp(1700000000);

        $this->assertSame($recordedAt, Explanation::of([], 0, $recordedAt)->recordedAt());
        $this->assertSame($recordedAt, Explanation::everything('a reason', $recordedAt)->recordedAt());
        $this->assertNull(Explanation::everything('nothing is recorded', null)->recordedAt());
    }
}
