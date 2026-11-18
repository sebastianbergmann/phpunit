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

use Countable;
use IteratorAggregate;

/**
 * Records, in the order in which they happen, the invocations of methods of
 * mock objects that were registered using MockObject::recordInvocationsIn().
 *
 * This interface is not meant to be implemented outside of PHPUnit: an
 * invocation journal is created using TestCase::createInvocationJournal().
 *
 * @template-extends IteratorAggregate<non-negative-int, non-empty-string>
 *
 * @phpstan-sealed InvocationJournalInternal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface InvocationJournal extends Countable, IteratorAggregate
{
    /**
     * @return list<non-empty-string>
     */
    public function asArray(): array;

    /**
     * @param non-empty-string $label
     * @param non-empty-string ...$additionalLabels
     *
     * @return list<non-empty-string>
     */
    public function only(string $label, string ...$additionalLabels): array;
}
