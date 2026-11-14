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

use PHPUnit\Framework\Attributes\StateMachineRule;

final class StateMachineWithRuleThatReturnsAGivenValue
{
    public mixed $value = null;

    #[StateMachineRule]
    public function value(): mixed
    {
        return $this->value;
    }
}
