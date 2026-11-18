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

use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

/**
 * This interface is not meant to be implemented outside of PHPUnit: a mock
 * object is created by PHPUnit, for instance using TestCase::createMock().
 *
 * @phpstan-sealed MockObjectInternal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface MockObject extends Stub
{
    public function expects(InvocationOrder $invocationRule): InvocationMocker;

    /**
     * @param ?non-empty-string                         $label
     * @param array<non-empty-string, non-empty-string> $methodLabels
     */
    public function recordInvocationsIn(InvocationJournal $journal, ?string $label = null, array $methodLabels = []): void;
}
