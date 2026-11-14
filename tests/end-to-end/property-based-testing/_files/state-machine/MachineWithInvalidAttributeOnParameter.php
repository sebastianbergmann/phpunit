<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\StateMachine;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\StateMachineRule;

final class MachineWithInvalidAttributeOnParameter
{
    #[StateMachineRule(produces: 'values')]
    public function produce(): int
    {
        return 1;
    }

    #[StateMachineRule]
    public function consume(#[FromPool('values', consumed: true)] int $value): void
    {
        Assert::assertSame(1, $value);
    }
}
