<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\RegisterSubscribers;

use const PHP_EOL;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;

final readonly class MyExecutionStartedSubscriber implements ExecutionStartedSubscriber
{
    public function notify(ExecutionStarted $event): void
    {
        print __METHOD__ . PHP_EOL;
    }
}
