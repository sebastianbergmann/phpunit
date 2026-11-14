<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\StateMachineRule;
use PHPUnit\Framework\TestCase;

final class StateMachineThatEndsTheProcessTest extends TestCase
{
    #[Property]
    public function testExitsInRule(): void
    {
        $this->runStateMachine(new CounterThatEndsTheProcess);
    }
}

final class CounterThatEndsTheProcess
{
    private int $counter = 0;

    /**
     * @param int<1, 10> $by
     */
    #[StateMachineRule]
    public function increment(int $by): void
    {
        $this->counter += $by;

        if ($this->counter > 25) {
            exit(1);
        }
    }

    #[StateMachineRule]
    public function reset(): void
    {
        $this->counter = 0;
    }
}
