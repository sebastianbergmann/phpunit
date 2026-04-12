<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestRunnerStopping;

use const SIGINT;
use function posix_getppid;
use function posix_kill;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class InterruptProcessIsolationTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testOne(): void
    {
        $this->assertTrue(true);

        posix_kill(posix_getppid(), SIGINT);
    }

    #[RunInSeparateProcess]
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
