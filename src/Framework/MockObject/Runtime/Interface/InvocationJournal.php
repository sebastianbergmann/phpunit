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

/**
 * Records, in the order in which they happen, the invocations of methods that
 * were configured using InvocationMocker::recordIn().
 */
interface InvocationJournal extends Countable
{
    /**
     * @return list<non-empty-string>
     */
    public function invocations(): array;
}
