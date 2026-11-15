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

use const PHP_EOL;
use PHPUnit\Event\TestRunner\ChildProcessErrored;
use PHPUnit\Event\TestRunner\ChildProcessErroredSubscriber;

final class ErroredSubscriber implements ChildProcessErroredSubscriber
{
    public function notify(ChildProcessErrored $event): void
    {
        print 'child process that errored ' . ExitStatus::describe($event->exitCode(), $event->signal()) . PHP_EOL;
    }
}
