<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Repeat;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class DependsTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('testOne')]
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
