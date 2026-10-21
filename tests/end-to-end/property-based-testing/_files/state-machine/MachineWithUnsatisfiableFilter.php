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

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Rule;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

final class MachineWithUnsatisfiableFilter
{
    /**
     * @return ValueGenerator<int>
     */
    public static function values(): ValueGenerator
    {
        return Generators::integers(0, 9)->filter(static fn (int $value): bool => $value > 9);
    }

    #[Rule]
    public function step(#[ForAll('values')] int $value): void
    {
    }
}
