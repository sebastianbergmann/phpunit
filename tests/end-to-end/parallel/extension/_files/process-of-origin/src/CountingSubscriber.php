<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\ProcessOfOrigin;

use const FILE_APPEND;
use const LOCK_EX;
use const PHP_EOL;
use function file_put_contents;
use function getenv;
use function getmypid;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

/**
 * Counts a finished test once, in the process that ran it: the events that
 * the main process replays for a test that ran in a worker process carry the
 * ID of the worker process and are skipped.
 */
final class CountingSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        if ($event->telemetryInfo()->processId() !== getmypid()) {
            return;
        }

        $process = 'main process';

        if (getenv('PHPUNIT_WORKER_ID') !== false) {
            $process = 'worker process';
        }

        file_put_contents(
            (string) getenv('PHPUNIT_TEST_COUNTED_LOG'),
            $event->test()->id() . ' counted in ' . $process . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}
