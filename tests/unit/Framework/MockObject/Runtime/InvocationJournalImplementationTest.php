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
#[Group('test-doubles')]
#[Small]
final class InvocationJournalImplementationTest extends TestCase
{
    public function testIsEmptyWhenNoInvocationHasBeenRecorded(): void
    {
        $journal = new InvocationJournalImplementation;

        $this->assertSame([], $journal->invocations());
        $this->assertCount(0, $journal);
    }

    public function testRecordsInvocationsInTheOrderInWhichTheyHappen(): void
    {
        $journal = new InvocationJournalImplementation;

        $journal->record('first');
        $journal->record('second');

        $this->assertSame(['first', 'second'], $journal->invocations());
        $this->assertCount(2, $journal);
    }
}
