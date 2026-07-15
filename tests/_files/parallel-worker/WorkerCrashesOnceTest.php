<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorker;

use function file_put_contents;
use function is_file;
use PHPUnit\Framework\TestCase;

final class WorkerCrashesOnceTest extends TestCase
{
    public function testThatCrashesOnTheFirstAttempt(string $marker): void
    {
        // The marker file survives across the attempts: it is absent on the
        // first attempt, which kills the worker before it can report, and
        // present on the retry, which passes.
        if (!is_file($marker)) {
            file_put_contents($marker, 'first attempt');

            exit(1);
        }

        $this->assertFileExists($marker);
    }
}
