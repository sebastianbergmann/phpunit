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
use PHPUnit\Framework\Attributes\UsesFixture;
use PHPUnit\Framework\TestCase;

#[UsesFixture('../fixtures/sums.csv')]
abstract class TestCaseThatReadsSums extends TestCase
{
    /**
     * @return list<list<int>>
     */
    protected function sums(): array
    {
        $rows = [];

        foreach (file(__DIR__ . '/../fixtures/sums.csv', FILE_IGNORE_NEW_LINES) as $line) {
            $rows[] = array_map('intval', explode(',', $line));
        }

        return $rows;
    }
}
