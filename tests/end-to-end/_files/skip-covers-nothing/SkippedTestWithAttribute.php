<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\SkipCoversNothing;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

final class SkippedTestWithAttribute extends TestCase
{
    #[CoversNothing]
    public function testCoversNothingWithAttribute(): void
    {
        $this->assertTrue(true);
    }

    public function testCoversNothingWithoutAttribute(): void
    {
        $this->assertTrue(true);
    }
}
