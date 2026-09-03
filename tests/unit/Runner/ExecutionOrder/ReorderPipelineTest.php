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

use function array_key_last;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\ExecutionOrder\Stage\ByDuration;
use PHPUnit\Runner\ExecutionOrder\Stage\BySize;
use PHPUnit\Runner\ExecutionOrder\Stage\DefectsFirst;
use PHPUnit\Runner\ExecutionOrder\Stage\Randomize;
use PHPUnit\Runner\ExecutionOrder\Stage\ResolveDependencies;
use PHPUnit\Runner\ExecutionOrder\Stage\Reverse;
use PHPUnit\Runner\TestSuiteSorter;

#[CoversClass(ReorderPipeline::class)]
#[UsesClass(ByDuration::class)]
#[UsesClass(BySize::class)]
#[UsesClass(DefaultDefectWeightPolicy::class)]
#[UsesClass(DefectsFirst::class)]
#[UsesClass(Randomize::class)]
#[UsesClass(ResolveDependencies::class)]
#[UsesClass(Reverse::class)]
#[Small]
final class ReorderPipelineTest extends TestCase
{
    /**
     * @return non-empty-list<array{list<non-empty-string>, int, int, bool}>
     */
    public static function provider(): array
    {
        return [
            'nothing configured' => [
                [],
                TestSuiteSorter::ORDER_DEFAULT,
                TestSuiteSorter::ORDER_DEFAULT,
                false,
            ],

            'dependency resolution only' => [
                ['depends'],
                TestSuiteSorter::ORDER_DEFAULT,
                TestSuiteSorter::ORDER_DEFAULT,
                true,
            ],

            'defects only' => [
                ['defects'],
                TestSuiteSorter::ORDER_DEFAULT,
                TestSuiteSorter::ORDER_DEFECTS_FIRST,
                false,
            ],

            'reverse' => [
                ['reverse'],
                TestSuiteSorter::ORDER_REVERSED,
                TestSuiteSorter::ORDER_DEFAULT,
                false,
            ],

            'random' => [
                ['random'],
                TestSuiteSorter::ORDER_RANDOMIZED,
                TestSuiteSorter::ORDER_DEFAULT,
                false,
            ],

            'duration ascending' => [
                ['duration-ascending'],
                TestSuiteSorter::ORDER_DURATION_ASCENDING,
                TestSuiteSorter::ORDER_DEFAULT,
                false,
            ],

            'duration descending' => [
                ['duration-descending'],
                TestSuiteSorter::ORDER_DURATION_DESCENDING,
                TestSuiteSorter::ORDER_DEFAULT,
                false,
            ],

            'size ascending' => [
                ['size-ascending'],
                TestSuiteSorter::ORDER_SIZE_ASCENDING,
                TestSuiteSorter::ORDER_DEFAULT,
                false,
            ],

            'size descending' => [
                ['size-descending'],
                TestSuiteSorter::ORDER_SIZE_DESCENDING,
                TestSuiteSorter::ORDER_DEFAULT,
                false,
            ],

            'everything' => [
                ['duration-ascending', 'defects', 'depends'],
                TestSuiteSorter::ORDER_DURATION_ASCENDING,
                TestSuiteSorter::ORDER_DEFECTS_FIRST,
                true,
            ],
        ];
    }

    /**
     * @param list<non-empty-string> $expected
     */
    #[DataProvider('provider')]
    public function testCompilesConfigurationToStages(array $expected, int $order, int $orderDefects, bool $resolveDependencies): void
    {
        $pipeline = ReorderPipeline::fromConfiguration($order, $orderDefects, $resolveDependencies);

        $this->assertSame($expected, $pipeline->describe());
        $this->assertSame($expected === [], $pipeline->isEmpty());
    }

    #[TestDox('Dependency resolution is always the last stage')]
    public function testDependencyResolutionIsAlwaysTheLastStage(): void
    {
        $stages = ReorderPipeline::fromConfiguration(
            TestSuiteSorter::ORDER_SIZE_DESCENDING,
            TestSuiteSorter::ORDER_DEFECTS_FIRST,
            true,
        )->describe();

        $this->assertSame('depends', $stages[array_key_last($stages)]);
    }
}
