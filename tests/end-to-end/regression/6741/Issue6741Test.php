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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Issue6741Test extends TestCase
{
    /**
     * @return array<non-empty-string, array{positive-int}>
     */
    public static function provider(): array
    {
        return [
            'case1' => [1],
            'case2' => [2],
            'case3' => [3],
            'case4' => [4],
        ];
    }

    #[DataProvider('provider')]
    public function testWithNamedDataSets(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }
}
