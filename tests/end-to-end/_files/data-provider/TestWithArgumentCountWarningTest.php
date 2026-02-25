<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DataProvider;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class TestWithArgumentCountWarningTest extends TestCase
{
    #[TestWith([1, 2, 3])]
    public function testOne(int $a, int $b): void
    {
        $this->assertGreaterThan(0, $a + $b);
    }
}
