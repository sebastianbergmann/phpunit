<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\Worker;

use const FILE_APPEND;
use const PHP_EOL;
use function file_put_contents;
use function getenv;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber as FinishedSubscriberInterface;

/**
 * Appends one line per finished test to a file named by the run, so that the
 * test can count how many times a worker-side subscriber saw the event: once
 * per test, never again when the main process replays it.
 */
final class FinishedSubscriber implements FinishedSubscriberInterface
{
    public function notify(Finished $event): void
    {
        file_put_contents(
            (string) getenv('PHPUNIT_TEST_FINISHED_LOG'),
            $event->test()->id() . ' finished in worker ' . getenv('PHPUNIT_WORKER_ID') . PHP_EOL,
            FILE_APPEND,
        );
    }
}
