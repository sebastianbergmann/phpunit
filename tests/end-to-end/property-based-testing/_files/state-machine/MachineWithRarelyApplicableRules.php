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

use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\Initialize;
use PHPUnit\Framework\Attributes\Rule;

/**
 * Its rules can only be applied when it is initialized with one particular
 * level of a thousand.
 */
final class MachineWithRarelyApplicableRules
{
    private int $level = 0;

    /**
     * @param int<0, 999> $level
     */
    #[Initialize]
    public function initialize(int $level): void
    {
        $this->level = $level;
    }

    #[Rule(precondition: 'isOpen', produces: 'tokens')]
    public function token(): int
    {
        return 1;
    }

    #[Rule]
    public function useToken(#[FromPool('tokens')] int $token): void
    {
    }

    public function isOpen(): bool
    {
        return $this->level === 500;
    }
}
