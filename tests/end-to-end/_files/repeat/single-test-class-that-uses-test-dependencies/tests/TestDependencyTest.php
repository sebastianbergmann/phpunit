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

final class TestDependencyTest extends TestCase
{
    public function testOne(): bool
    {
        $value = true;

        $this->assertTrue($value);

        return $value;
    }

    #[Depends('testOne')]
    public function testTwo(bool $value): void
    {
        $this->assertTrue($value);
    }
}
