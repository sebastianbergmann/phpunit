<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvocationJournalImplementation::class)]
#[CoversClass(InvocationJournalIterator::class)]
#[Group('test-doubles')]
#[Small]
final class InvocationJournalImplementationTest extends TestCase
{
    public function testIsEmptyWhenNoInvocationHasBeenRecorded(): void
    {
        $journal = new InvocationJournalImplementation;

        $this->assertSame([], $journal->asArray());
        $this->assertCount(0, $journal);
    }

    public function testRecordsInvocationsInTheOrderInWhichTheyHappen(): void
    {
        $journal = new InvocationJournalImplementation;

        $journal->record('first');
        $journal->record('second');

        $this->assertSame(['first', 'second'], $journal->asArray());
        $this->assertCount(2, $journal);
    }

    public function testOnlyReturnsRecordedInvocationsWithGivenLabels(): void
    {
        $journal = new InvocationJournalImplementation;

        $journal->record('first');
        $journal->record('noise');
        $journal->record('second');
        $journal->record('noise');
        $journal->record('first');

        $this->assertSame(['first', 'second', 'first'], $journal->only('first', 'second'));
        $this->assertSame(['noise', 'noise'], $journal->only('noise'));
    }

    public function testOnlyReturnsEmptyArrayWhenNoRecordedInvocationHasGivenLabel(): void
    {
        $journal = new InvocationJournalImplementation;

        $journal->record('first');

        $this->assertSame([], $journal->only('second'));
    }

    public function testCanBeIterated(): void
    {
        $journal = new InvocationJournalImplementation;

        $journal->record('first');
        $journal->record('second');

        $recorded = [];

        foreach ($journal as $position => $invocation) {
            $recorded[$position] = $invocation;
        }

        $this->assertSame([0 => 'first', 1 => 'second'], $recorded);
    }
}
