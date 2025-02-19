<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

use function assert;
use function file_get_contents;
use function is_file;
use function unlink;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\ChildProcessResultProcessor;
use PHPUnit\Framework\Test;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class JobRunner
{
    private ChildProcessResultProcessor $processor;

    public function __construct(ChildProcessResultProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param non-empty-string $processResultFile
     */
    final public function runTestJob(Job $job, string $processResultFile, Test $test): void
    {
        $result = $this->run($job);

        $processResult = '';

        if (is_file($processResultFile)) {
            $processResult = file_get_contents($processResultFile);

            assert($processResult !== false);

            @unlink($processResultFile);
        }

        $this->processor->process(
            $test,
            $processResult,
            $result->stderr(),
        );

        EventFacade::emitter()->testRunnerFinishedChildProcess($result->stdout(), $result->stderr());
    }

    abstract public function run(Job $job): Result;
}
