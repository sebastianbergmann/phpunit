<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\InitializeStateMachine;
use PHPUnit\Framework\Attributes\StateMachineInvariant;
use PHPUnit\Framework\Attributes\StateMachineRule;

final class StateMachine
{
    #[InitializeStateMachine('accounts')]
    public function initialize(): string
    {
        return 'account';
    }

    #[StateMachineRule]
    public function rule(): void
    {
    }

    #[StateMachineRule('canWithdraw', 'receipts')]
    public function withdraw(#[FromPool('accounts')] string $account, int $amount, #[FromPool('receipts', true)] string $receipt): string
    {
        return 'receipt';
    }

    public function canWithdraw(): bool
    {
        return true;
    }

    #[StateMachineInvariant]
    public function invariant(): void
    {
    }

    #[StateMachineInvariant(true)]
    public function invariantAtEnd(): void
    {
    }
}
