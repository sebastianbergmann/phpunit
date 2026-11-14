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

use function sprintf;
use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\StateMachineRule;

final class LockedMachine
{
    /**
     * @var list<non-empty-string>
     */
    public array $calls  = [];
    private bool $locked = true;

    /**
     * @param int<0, 9> $amount
     */
    #[StateMachineRule(precondition: 'isUnlocked')]
    public function add(int $amount): void
    {
        $this->calls[] = sprintf('add(%d)', $amount);
    }

    #[StateMachineRule]
    public function take(#[FromPool('items')] int $item): void
    {
        $this->calls[] = sprintf('take(%d)', $item);
    }

    #[StateMachineRule(produces: 'items')]
    public function item(): int
    {
        $this->calls[] = 'item()';

        return 1;
    }

    #[StateMachineRule(precondition: 'isUnlocked')]
    public function inspect(#[FromPool('items')] int $item): void
    {
        $this->calls[] = sprintf('inspect(%d)', $item);
    }

    #[StateMachineRule]
    public function unlock(): void
    {
        $this->calls[] = 'unlock()';

        $this->locked = false;
    }

    public function isUnlocked(): bool
    {
        return !$this->locked;
    }
}
