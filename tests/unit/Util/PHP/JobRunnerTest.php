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

#[CoversClass(JobRunner::class)]
#[UsesClass(Job::class)]
#[UsesClass(Result::class)]
#[Small]
final class JobRunnerTest extends TestCase
{
    public static function provider(): Generator
    {
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

        $obfuscationRegex = '(?i)(?:(?:"|%22)?)(?:(?:old[-_]?|new[-_]?)?p(?:ass)?w(?:or)?d(?:1|2)?|pass(?:[-_]?phrase)?|secret|(?:api_?|private_?|public_?|access_?|secret_?)key(?:_?id)?|token|consumer_?(?:id|key|secret)|sign(?:ed|ature)?|auth(?:entication|orization)?)(?:(?:\s|%20)*(?:=|%3D)[^&]+|(?:"|%22)(?:\s|%20)*(?::|%3A)(?:\s|%20)*(?:"|%22)(?:%2[^2]|%[^2]|[^"%])+(?:"|%22))|bearer(?:\s|%20)+[a-z0-9\._\-]+|token(?::|%3A)[a-z0-9]{13}|gh[opsu]_[0-9a-zA-Z]{36}|ey[I-L](?:[\w=-]|%3D)+\.ey[I-L](?:[\w=-]|%3D)+(?:\.(?:[\w.+\/=-]|%3D|%2F|%2B)+)?|-{5}BEGIN(?:[a-z\s]|%20)+PRIVATE(?:\s|%20)KEY-{5}[^\-]+-{5}END(?:[a-z\s]|%20)+PRIVATE(?:\s|%20)KEY(?:-{5})?(?:\n|%0A)?';

        yield 'PHP setting value containing INI metacharacters' => [
            new Result($obfuscationRegex, ''),
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
print ini_get('highlight.string');

EOT,
                phpSettings: ['highlight.string=' . $obfuscationRegex],
            ),
        ];

        yield 'PHP setting without value' => [
            new Result('1', ''),
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
print ini_get('highlight.string');

EOT,
                phpSettings: ['highlight.string'],
            ),
        ];
    }

    #[DataProvider('provider')]
    public function testRunsJobInSeparateProcess(Result $expected, Job $job): void
    {
        $jobRunner = new JobRunner(
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

    public function testRejectsPhpSettingValueContainingLineBreak(): void
    {
        $jobRunner = new JobRunner(
            new ChildProcessResultProcessor(
                new Facade,
                $this->createStub(Emitter::class),
                new PassedTests,
                new CodeCoverage,
            ),
        );

        $job = new Job(
            <<<'EOT'
<?php declare(strict_types=1);

EOT,
            phpSettings: ["highlight.string=foo\nauto_prepend_file=/tmp/evil.php"],
        );

        $this->expectException(PhpProcessException::class);
        $this->expectExceptionMessage('PHP setting "highlight.string" contains a line-break character, which is not permitted');

        $jobRunner->run($job);
    }
}
