<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class RepeatDependentTest extends TestCase
{
    public function test1(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('test1')]
    public function testDepends1(): void
    {
        $this->assertTrue(true);
    }

    public function test2(): void
    {
        $this->assertTrue(false);
    }

    #[Depends('test2')]
    public function testDepends2(): void
    {
        $this->assertTrue(true);
    }
}
