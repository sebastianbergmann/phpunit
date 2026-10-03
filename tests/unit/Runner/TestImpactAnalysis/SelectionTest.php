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

#[CoversClass(Selection::class)]
#[UsesClass(RecordingTime::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class SelectionTest extends TestCase
{
    public function testKnowsThatEveryTestIsRun(): void
    {
        $selection = Selection::everything('a reason', null);

        $this->assertTrue($selection->isEverything());
        $this->assertSame('a reason', $selection->reason());
        $this->assertSame([], $selection->tests());
        $this->assertSame(0, $selection->numberOfTestsThatAreNotRun());
    }

    public function testKnowsWhichTestsAreRun(): void
    {
        $selection = Selection::of(['FooTest::testOne'], 'a reason', 3, RecordingTime::fromUnixTimestamp(1700000000));

        $this->assertFalse($selection->isEverything());
        $this->assertSame(['FooTest::testOne'], $selection->tests());
        $this->assertSame('a reason', $selection->reason());
    }

    public function testKnowsThatNoTestCanBeAffectedByWhatChanged(): void
    {
        $this->assertTrue(Selection::of([], 'nothing changed', 2, RecordingTime::fromUnixTimestamp(1700000000))->isNothing());
        $this->assertFalse(Selection::of(['FooTest::testOne'], 'something changed', 2, RecordingTime::fromUnixTimestamp(1700000000))->isNothing());
        $this->assertFalse(Selection::everything('nothing is known', null)->isNothing());
    }

    public function testKnowsHowManyTestsAreNotRun(): void
    {
        $this->assertSame(2, Selection::of(['FooTest::testOne'], 'a reason', 3, RecordingTime::fromUnixTimestamp(1700000000))->numberOfTestsThatAreNotRun());
    }

    public function testKnowsWhenWhatItWasMadeFromWasRecorded(): void
    {
        $recordedAt = RecordingTime::fromUnixTimestamp(1700000000);

        $this->assertSame($recordedAt, Selection::of([], 'a reason', 1, $recordedAt)->recordedAt());
        $this->assertSame($recordedAt, Selection::everything('a reason', $recordedAt)->recordedAt());
        $this->assertNull(Selection::everything('nothing is recorded', null)->recordedAt());
    }
}
