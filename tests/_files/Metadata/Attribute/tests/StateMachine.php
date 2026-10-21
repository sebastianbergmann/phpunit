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
use PHPUnit\Framework\Attributes\Initialize;
use PHPUnit\Framework\Attributes\Invariant;
use PHPUnit\Framework\Attributes\Rule;

final class StateMachine
{
    #[Initialize(produces: 'accounts')]
    public function initialize(): string
    {
        return 'account';
    }

    #[Rule]
    public function rule(): void
    {
    }

    #[Rule(precondition: 'canWithdraw', produces: 'receipts')]
    public function withdraw(#[FromPool('accounts')] string $account, int $amount, #[FromPool('receipts', consume: true)] string $receipt): string
    {
        return 'receipt';
    }

    public function canWithdraw(): bool
    {
        return true;
    }

    #[Invariant]
    public function invariant(): void
    {
    }

    #[Invariant(atEnd: true)]
    public function invariantAtEnd(): void
    {
    }
}
