<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelAuto;

use PHPUnit\Framework\TestCase;

final class SecondAutoTest extends TestCase
{
    public function testTwo(): void
    {
        $this->assertSame(2, 1 + 1);
    }
}
