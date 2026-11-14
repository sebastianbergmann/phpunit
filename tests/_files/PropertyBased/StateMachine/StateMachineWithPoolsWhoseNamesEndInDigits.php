<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\StateMachine;

use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\StateMachineRule;

final class StateMachineWithPoolsWhoseNamesEndInDigits
{
    #[StateMachineRule(produces: 'x')]
    public function produceX(): int
    {
        return 1;
    }

    #[StateMachineRule(produces: 'x1')]
    public function produceX1(): int
    {
        return 2;
    }

    #[StateMachineRule(produces: 'x_1')]
    public function produceX_1(): int
    {
        return 3;
    }

    #[StateMachineRule]
    public function consume(#[FromPool('x', consume: true)] int $x, #[FromPool('x1')] int $x1, #[FromPool('x_1')] int $x_1): void
    {
    }
}
