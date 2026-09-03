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
use PHPUnit\Runner\ExecutionOrder\Stage\BySize;
use PHPUnit\Runner\ExecutionOrder\Stage\DefectsFirst;
use PHPUnit\Runner\ExecutionOrder\Stage\Randomize;
use PHPUnit\Runner\ExecutionOrder\Stage\ResolveDependencies;
use PHPUnit\Runner\ExecutionOrder\Stage\Reverse;
use PHPUnit\Runner\InvalidOrderException;
use PHPUnit\Runner\TestSuiteSorter;

/**
 * An ordered list of reordering stages that is applied to the tests of every
 * test suite.
 *
 * The stage order is currently derived from the configuration in a fixed way:
 * the main order first, then the "defects first" overlay, then dependency
 * resolution. PHPUnit 14 will derive the stage order from the order in which
 * the configuration tokens are written instead.
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
     * Dependency resolution is always the last stage: any stage applied after
     * it would undo the order it establishes.
     *
     * @throws InvalidOrderException
     */
    public static function fromConfiguration(int $order, int $orderDefects, bool $resolveDependencies): self
    {
        $stages = [];

        $mainOrder = self::mainOrderStage($order);

        if ($mainOrder !== null) {
            $stages[] = $mainOrder;
        }

        if ($orderDefects === TestSuiteSorter::ORDER_DEFECTS_FIRST) {
            $stages[] = new DefectsFirst(new DefaultDefectWeightPolicy);
        } elseif ($orderDefects !== TestSuiteSorter::ORDER_DEFAULT) {
            // @codeCoverageIgnoreStart
            throw new InvalidOrderException;
            // @codeCoverageIgnoreEnd
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
     * The configuration tokens of the stages of this pipeline, in the order in
     * which they are applied.
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

    /**
     * @throws InvalidOrderException
     */
    private static function mainOrderStage(int $order): ?ReorderStage
    {
        if ($order === TestSuiteSorter::ORDER_DEFAULT) {
            return null;
        }

        if ($order === TestSuiteSorter::ORDER_REVERSED) {
            return new Reverse;
        }

        if ($order === TestSuiteSorter::ORDER_RANDOMIZED) {
            return new Randomize;
        }

        if ($order === TestSuiteSorter::ORDER_DURATION_ASCENDING) {
            return new ByDuration(Direction::Ascending);
        }

        if ($order === TestSuiteSorter::ORDER_DURATION_DESCENDING) {
            return new ByDuration(Direction::Descending);
        }

        if ($order === TestSuiteSorter::ORDER_SIZE_ASCENDING) {
            return new BySize(Direction::Ascending);
        }

        if ($order === TestSuiteSorter::ORDER_SIZE_DESCENDING) {
            return new BySize(Direction::Descending);
        }

        // @codeCoverageIgnoreStart
        throw new InvalidOrderException;
        // @codeCoverageIgnoreEnd
    }
}
