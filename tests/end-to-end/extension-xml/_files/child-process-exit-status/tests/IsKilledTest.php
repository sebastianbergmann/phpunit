<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\ChildProcessExitStatus;

use function getmypid;
use function posix_kill;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class IsKilledTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testOne(): void
    {
        posix_kill(getmypid(), 9);
    }
}
