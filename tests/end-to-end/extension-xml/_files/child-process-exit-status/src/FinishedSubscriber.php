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
use PHPUnit\Event\TestRunner\ChildProcessFinished;
use PHPUnit\Event\TestRunner\ChildProcessFinishedSubscriber;

final class FinishedSubscriber implements ChildProcessFinishedSubscriber
{
    public function notify(ChildProcessFinished $event): void
    {
        print 'child process that finished ' . ExitStatus::describe($event->exitCode(), $event->signal()) . PHP_EOL;
    }
}
