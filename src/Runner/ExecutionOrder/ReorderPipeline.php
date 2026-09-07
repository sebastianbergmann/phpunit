<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ExecutionOrder;

use PHPUnit\Framework\Test;
use PHPUnit\Runner\ExecutionOrder\Stage\ByDuration;
use PHPUnit\Runner\ExecutionOrder\Stage\ByModificationTime;
use PHPUnit\Runner\ExecutionOrder\Stage\BySize;
use PHPUnit\Runner\ExecutionOrder\Stage\DefectsFirst;
use PHPUnit\Runner\ExecutionOrder\Stage\Randomize;
use PHPUnit\Runner\ExecutionOrder\Stage\ResolveDependencies;
use PHPUnit\Runner\ExecutionOrder\Stage\Reverse;

/**
 * An ordered list of reordering stages that is applied to the tests of every
 * test suite.
 *
 * The stages are applied in the order in which they were configured. Resolving
 * dependencies between tests is not a reordering strategy and is not configured
 * through that list; it is always applied last, because any stage applied after
 * it would undo the order it establishes.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ReorderPipeline
{
    /**
     * @var list<ReorderStage>
     */
    private array $stages;

    /**
     * @param list<Order> $order
     */
    public static function fromConfiguration(array $order, bool $resolveDependencies): self
    {
        $stages = [];

        foreach ($order as $element) {
            $stages[] = self::stageFor($element);
        }

        if ($resolveDependencies) {
            $stages[] = new ResolveDependencies;
        }

        return new self($stages);
    }

    /**
     * @param list<ReorderStage> $stages
     */
    public function __construct(array $stages)
    {
        $this->stages = $stages;
    }

    public function isEmpty(): bool
    {
        return $this->stages === [];
    }

    /**
     * The names of the stages of this pipeline, in the order in which they are
     * applied.
     *
     * @return list<non-empty-string>
     */
    public function describe(): array
    {
        $names = [];

        foreach ($this->stages as $stage) {
            $names[] = $stage->name();
        }

        return $names;
    }

    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    public function apply(array $tests, Context $context): array
    {
        foreach ($this->stages as $stage) {
            $tests = $stage->apply($tests, $context);
        }

        return $tests;
    }

    private static function stageFor(Order $order): ReorderStage
    {
        return match ($order) {
            Order::Defects            => new DefectsFirst(new DefaultDefectWeightPolicy),
            Order::DurationAscending  => new ByDuration(Direction::Ascending),
            Order::DurationDescending => new ByDuration(Direction::Descending),
            Order::ModifiedAscending  => new ByModificationTime(Direction::Ascending),
            Order::ModifiedDescending => new ByModificationTime(Direction::Descending),
            Order::Random             => new Randomize,
            Order::Reverse            => new Reverse,
            Order::SizeAscending      => new BySize(Direction::Ascending),
            Order::SizeDescending     => new BySize(Direction::Descending),
        };
    }
}
