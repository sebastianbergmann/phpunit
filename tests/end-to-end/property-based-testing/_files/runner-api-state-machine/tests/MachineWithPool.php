<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\StateMachineRule;

final class MachineWithPool
{
    #[StateMachineRule(produces: 'items')]
    public function item(): int
    {
        return 1;
    }

    #[StateMachineRule]
    public function inspect(#[FromPool('items')] int $item): void
    {
    }

    #[StateMachineRule]
    public function take(#[FromPool('items', consume: true)] int $item): void
    {
    }
}
