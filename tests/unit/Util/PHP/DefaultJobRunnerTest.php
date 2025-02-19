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

use const PHP_VERSION;
use function version_compare;
use Generator;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\ChildProcessResultProcessor;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;

#[CoversClass(DefaultJobRunner::class)]
#[UsesClass(Job::class)]
#[UsesClass(Result::class)]
#[Small]
final class DefaultJobRunnerTest extends TestCase
{
    public static function provider(): Generator
    {
        if (version_compare(PHP_VERSION, '8.3.0', '>')) {
            yield 'output to stdout' => [
                new Result('test', ''),
                new Job(
                    <<<'EOT'
<?php declare(strict_types=1);
fwrite(STDOUT, 'test');

EOT
                ),
            ];

            yield 'output to stderr' => [
                new Result('', 'test'),
                new Job(
                    <<<'EOT'
<?php declare(strict_types=1);
fwrite(STDERR, 'test');

EOT
                ),
            ];

            yield 'output to stdout and stderr' => [
                new Result('test-stdout', 'test-stderr'),
                new Job(
                    <<<'EOT'
<?php declare(strict_types=1);
fwrite(STDOUT, 'test-stdout');
fwrite(STDERR, 'test-stderr');

EOT
                ),
            ];

            yield 'stderr redirected to stdout' => [
                new Result('test', ''),
                new Job(
                    <<<'EOT'
<?php declare(strict_types=1);
fwrite(STDERR, 'test');

EOT,
                    redirectErrors: true,
                ),
            ];
        }

        yield 'configured environment variables' => [
            new Result('test', ''),
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
print getenv('test');

EOT,
                environmentVariables: ['test' => 'test'],
            ),
        ];

        yield 'arguments' => [
            new Result('test', ''),
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
print $argv[1];

EOT,
                arguments: ['test'],
            ),
        ];

        yield 'input from stdin' => [
            new Result('test', ''),
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
print file_get_contents('php://stdin');

EOT,
                input: 'test',
            ),
        ];
    }

    #[DataProvider('provider')]
    public function testRunsJobInSeparateProcess(Result $expected, Job $job): void
    {
        $jobRunner = new DefaultJobRunner(
            new ChildProcessResultProcessor(
                new Facade,
                $this->createStub(Emitter::class),
                new PassedTests,
                new CodeCoverage,
            ),
        );

        $result = $jobRunner->run($job);

        $this->assertSame($expected->stdout(), $result->stdout());
        $this->assertSame($expected->stderr(), $result->stderr());
    }
}
