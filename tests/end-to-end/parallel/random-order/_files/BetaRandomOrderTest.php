<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelRandomOrder;

use PHPUnit\Framework\TestCase;

final class BetaRandomOrderTest extends TestCase
{
    public function testThree(): void
    {
        $this->assertTrue(true);
    }

    public function testFour(): void
    {
        $this->assertTrue(true);
    }
}
