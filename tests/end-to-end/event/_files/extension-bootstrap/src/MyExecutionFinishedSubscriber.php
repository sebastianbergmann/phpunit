<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\MyExtension;

use const PHP_EOL;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;

final class MyExecutionFinishedSubscriber implements ExecutionFinishedSubscriber
{
    private readonly string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function notify(ExecutionFinished $event): void
    {
        print __METHOD__ . PHP_EOL;
        print $this->message . PHP_EOL;
    }
}
