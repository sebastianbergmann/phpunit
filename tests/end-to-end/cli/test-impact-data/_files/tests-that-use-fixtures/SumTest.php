<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData;

use const FILE_IGNORE_NEW_LINES;
use function array_map;
use function explode;
use function file;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesFixture;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]
final class SumTest extends TestCase
{
    #[UsesFixture('../fixtures/sums.csv')]
    public static function provideSums(): array
    {
        $rows = [];

        foreach (file(__DIR__ . '/../fixtures/sums.csv', FILE_IGNORE_NEW_LINES) as $line) {
            $rows[] = array_map('intval', explode(',', $line));
        }

        return $rows;
    }

    #[DataProvider('provideSums')]
    #[UsesFixture('../fixtures/does-not-exist.csv')]
    public function testAdds(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, (new Calculator)->add($a, $b));
    }
}
