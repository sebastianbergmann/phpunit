<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelExecution;

use PHPUnit\Framework\TestCase;

final class SecondParallelTest extends TestCase
{
    public function testThree(): void
    {
        $this->assertSame(3, 1 + 2);
    }

    public function testFour(): void
    {
        $this->assertSame(4, 5);
    }
}
