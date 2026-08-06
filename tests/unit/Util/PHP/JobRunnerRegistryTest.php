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

use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestFixture\Success;
use PHPUnit\TestRunner\TestResult\PassedTests;

#[CoversClass(JobRunnerRegistry::class)]
#[UsesClass(ChildProcessResultProcessor::class)]
#[UsesClass(Job::class)]
#[UsesClass(JobRunner::class)]
#[UsesClass(Result::class)]
#[UsesClass(RunningJob::class)]
#[Small]
final class JobRunnerRegistryTest extends TestCase
{
    protected function tearDown(): void
    {
        // restore the job runner that JobRunnerRegistry creates on demand
        JobRunnerRegistry::set(
            new JobRunner(
                new ChildProcessResultProcessor(
                    Facade::instance(),
                    Facade::emitter(),
                    PassedTests::instance(),
                    CodeCoverage::instance(),
                ),
            ),
        );
    }

    public function testConfiguredJobRunnerIsUsed(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testErrored')
            ->seal();

        JobRunnerRegistry::set(
            new JobRunner(
                new ChildProcessResultProcessor(
                    new Facade,
                    $emitter,
                    new PassedTests,
                    new CodeCoverage,
                ),
            ),
        );

        JobRunnerRegistry::runTestJob(
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
fwrite(STDERR, 'test');

EOT,
                ChildProcessReason::TestRequiringProcessIsolation,
            ),
            '/path/to/process-result-file/that/does/not/exist',
            new Success('testOne'),
        );
    }
}
