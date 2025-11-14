<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function pcntl_fork;
use function pcntl_wait;
use PHPUnit\Framework\TestCase;

final class ChildProcessTest extends TestCase
{
    public function testChildProcessOutput(): void
    {
        $child = pcntl_fork();
        $this->assertGreaterThan(-1, $child);

        if ($child) {
            pcntl_wait($child);
            $this->assertTrue(true);
        } else {
            exit(0);
        }
    }
}
