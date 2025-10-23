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

final class DependentOfTestFailedInRepetitionTest extends TestCase
{
    public function test1(): void
    {
        static $cout = 0;

        if ($cout++ > 0) {
            $this->assertFalse(true);
        }

        $this->assertTrue(true);
    }

    #[Depends('test1')]
    public function test2(): void
    {
        $this->assertTrue(true);
    }
}
