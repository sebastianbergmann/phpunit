<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6965;

use function pcntl_fork;
use function pcntl_waitpid;
use PHPUnit\Framework\TestCase;

final class Step2ForkTest extends TestCase
{
    public function testFork(): void
    {
        $pid = pcntl_fork();

        $this->assertGreaterThan(-1, $pid);

        if ($pid === 0) {
            // the forked process inherits the shutdown functions that were
            // registered by the process it was forked from
            exit(0);
        }

        pcntl_waitpid($pid, $status);

        $this->assertSame(0, $status);
    }
}
