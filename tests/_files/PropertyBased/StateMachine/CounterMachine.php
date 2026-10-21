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
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\Initialize;
use PHPUnit\Framework\Attributes\Invariant;
use PHPUnit\Framework\Attributes\Rule;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

final class CounterMachine
{
    /**
     * @var list<non-empty-string>
     */
    public array $calls  = [];
    private int $counter = 0;

    /**
     * @return ValueGenerator<int>
     */
    public static function startValues(): ValueGenerator
    {
        return Generators::integers(0, 3);
    }

    #[Initialize]
    public function start(#[ForAll('startValues')] int $value): void
    {
        $this->calls[] = sprintf('start(%d)', $value);

        $this->counter = $value;
    }

    #[Rule(produces: 'snapshots')]
    public function snapshot(): int
    {
        $this->calls[] = 'snapshot()';

        return $this->counter;
    }

    /**
     * @param int<1, 3> $amount
     */
    #[Rule]
    public function increment(int $amount): int
    {
        $this->calls[] = sprintf('increment(%d)', $amount);

        $this->counter += $amount;

        return $this->counter;
    }

    #[Rule(precondition: 'isPositive')]
    public function decrement(): void
    {
        $this->calls[] = 'decrement()';

        $this->counter--;
    }

    #[Rule]
    public function restore(#[FromPool('snapshots', consume: true)] int $snapshot): void
    {
        $this->calls[] = sprintf('restore(%d)', $snapshot);

        $this->counter = $snapshot;
    }

    public function isPositive(): bool
    {
        return $this->counter > 0;
    }

    #[Invariant]
    public function isNotNegative(): void
    {
        $this->calls[] = 'isNotNegative()';

        Assert::assertGreaterThanOrEqual(0, $this->counter);
    }

    #[Invariant(atEnd: true)]
    public function checkedAtEnd(): void
    {
        $this->calls[] = 'checkedAtEnd()';
    }
}
