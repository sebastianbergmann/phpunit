<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\EventsOfThisProcess;

use const FILE_APPEND;
use const LOCK_EX;
use const PHP_EOL;
use function file_put_contents;
use function getenv;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

final class CountingSubscriber implements FinishedSubscriber
{
    private string $process;

    public function __construct(string $process)
    {
        $this->process = $process;
    }

    public function notify(Finished $event): void
    {
        file_put_contents(
            (string) getenv('PHPUNIT_TEST_COUNTED_LOG'),
            $event->test()->id() . ' counted in ' . $this->process . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}
