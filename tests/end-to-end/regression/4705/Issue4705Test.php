<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function array_map;
use function gc_collect_cycles;
use function range;
use function str_repeat;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Issue4705Test extends TestCase
{
    public string $v;

    /**
     * @return list<array{int}>
     */
    public static function provideManyTests(): array
    {
        return array_map(static fn ($v) => [$v], range(1, 10_000));
    }

    /**
     * This test tests if the TestCase is released after run. If the TestCase
     * would not be released, 500+ GB of memory would be needed to not fail.
     */
    #[DataProvider('provideManyTests')]
    public function testAssign50MegabytesToTestCaseInstance(int $i): void
    {
        $this->v = str_repeat(str_repeat((string) ($i % 10), 1_000), 50_000);

        gc_collect_cycles();

        $this->assertTrue(true);
    }
}
