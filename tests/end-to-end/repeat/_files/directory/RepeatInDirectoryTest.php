<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace directory;

use PHPUnit\Framework\TestCase;

final class RepeatInDirectoryTest extends TestCase
{
    public function test1(): void
    {
        $this->assertTrue(true);
    }
}
