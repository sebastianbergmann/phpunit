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

final class DependentOfTestWhichReturnsSomethingTest extends TestCase
{
    public function test1(): string
    {
        $this->assertTrue(true);

        return 'foo';
    }

    #[Depends('test1')]
    public function test2(): void
    {
        $this->assertTrue(true);
    }
}
