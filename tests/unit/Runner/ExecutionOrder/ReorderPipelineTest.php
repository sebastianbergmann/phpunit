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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\ExecutionOrder\Stage\ByDuration;
use PHPUnit\Runner\ExecutionOrder\Stage\ByModificationTime;
use PHPUnit\Runner\ExecutionOrder\Stage\BySize;
use PHPUnit\Runner\ExecutionOrder\Stage\DefectsFirst;
use PHPUnit\Runner\ExecutionOrder\Stage\Randomize;
use PHPUnit\Runner\ExecutionOrder\Stage\ResolveDependencies;
use PHPUnit\Runner\ExecutionOrder\Stage\Reverse;

#[CoversClass(ReorderPipeline::class)]
#[CoversClass(Order::class)]
#[UsesClass(ByDuration::class)]
#[UsesClass(ByModificationTime::class)]
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
     * @return non-empty-list<array{list<non-empty-string>, list<Order>, bool}>
     */
    public static function provider(): array
    {
        return [
            'nothing configured' => [
                [],
                [],
                false,
            ],

            'dependency resolution only' => [
                ['resolve-dependencies'],
                [],
                true,
            ],

            'defects only' => [
                ['defects'],
                [Order::Defects],
                false,
            ],

            'reverse' => [
                ['reverse'],
                [Order::Reverse],
                false,
            ],

            'random' => [
                ['random'],
                [Order::Random],
                false,
            ],

            'duration ascending' => [
                ['duration-ascending'],
                [Order::DurationAscending],
                false,
            ],

            'duration descending' => [
                ['duration-descending'],
                [Order::DurationDescending],
                false,
            ],

            'modified ascending' => [
                ['modified-ascending'],
                [Order::ModifiedAscending],
                false,
            ],

            'modified descending' => [
                ['modified-descending'],
                [Order::ModifiedDescending],
                false,
            ],

            'size ascending' => [
                ['size-ascending'],
                [Order::SizeAscending],
                false,
            ],

            'size descending' => [
                ['size-descending'],
                [Order::SizeDescending],
                false,
            ],

            'order then defects' => [
                ['duration-ascending', 'defects', 'resolve-dependencies'],
                [Order::DurationAscending, Order::Defects],
                true,
            ],

            'defects then order' => [
                ['defects', 'duration-ascending', 'resolve-dependencies'],
                [Order::Defects, Order::DurationAscending],
                true,
            ],
        ];
    }

    /**
     * @param list<non-empty-string> $expected
     * @param list<Order>            $order
     */
    #[DataProvider('provider')]
    public function testCompilesConfigurationToStages(array $expected, array $order, bool $resolveDependencies): void
    {
        $pipeline = ReorderPipeline::fromConfiguration($order, $resolveDependencies);

        $this->assertSame($expected, $pipeline->describe());
        $this->assertSame($expected === [], $pipeline->isEmpty());
    }

    #[TestDox('Stages are applied in the order in which they were configured')]
    public function testStagesAreAppliedInTheOrderInWhichTheyWereConfigured(): void
    {
        $this->assertSame(
            ['defects', 'size-ascending'],
            ReorderPipeline::fromConfiguration([Order::Defects, Order::SizeAscending], false)->describe(),
        );

        $this->assertSame(
            ['size-ascending', 'defects'],
            ReorderPipeline::fromConfiguration([Order::SizeAscending, Order::Defects], false)->describe(),
        );
    }

    #[TestDox('Dependency resolution is always the last stage')]
    public function testDependencyResolutionIsAlwaysTheLastStage(): void
    {
        $stages = ReorderPipeline::fromConfiguration(
            [Order::Defects, Order::SizeDescending],
            true,
        )->describe();

        $this->assertSame(['defects', 'size-descending', 'resolve-dependencies'], $stages);
    }
}
