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
 * test doubles that were registered using Stub::recordInvocationsIn().
 *
 * @template-extends IteratorAggregate<non-negative-int, non-empty-string>
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
