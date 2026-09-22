<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use const DIRECTORY_SEPARATOR;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder as CliBuilder;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;

#[CoversClass(FileOutputTargets::class)]
#[CoversClass(FileOutputTarget::class)]
#[Medium]
#[Group('textui')]
#[Group('textui/configuration')]
final class FileOutputTargetsTest extends TestCase
{
    public function testOnlyTheTestRunHistoryIsWrittenByDefault(): void
    {
        $cliConfiguration = $this->cliConfiguration([]);

        $targets = FileOutputTargets::fromConfiguration($this->configuration($cliConfiguration), $cliConfiguration);

        $this->assertCount(1, $targets);
        $this->assertSame('test run history', $targets[0]->description());
        $this->assertStringEndsWith('.phpunit.result.cache', $targets[0]->path());
    }

    public function testNothingIsWrittenWhenNothingIsConfigured(): void
    {
        $cliConfiguration = $this->cliConfiguration(['--do-not-record-test-run-history']);

        $this->assertSame([], FileOutputTargets::fromConfiguration($this->configuration($cliConfiguration), $cliConfiguration));
    }

    public function testEnumeratesEveryConfiguredTarget(): void
    {
        $cacheDirectory     = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-' . uniqid();
        $temporaryDirectory = sys_get_temp_dir();

        $cliConfiguration = $this->cliConfiguration([
            '--cache-directory',
            $cacheDirectory,
            '--log-junit',
            'junit.xml',
            '--log-otr',
            'otr.xml',
            '--log-teamcity',
            'teamcity.txt',
            '--testdox-html',
            'testdox.html',
            '--testdox-text',
            'testdox.txt',
            '--log-events-text',
            $temporaryDirectory . '/events.txt',
            '--log-events-verbose-text',
            $temporaryDirectory . '/events-verbose.txt',
            '--coverage-clover',
            'clover.xml',
            '--coverage-cobertura',
            'cobertura.xml',
            '--coverage-crap4j',
            'crap4j.xml',
            '--coverage-html',
            'coverage-html',
            '--coverage-jsonl',
            'coverage.jsonl',
            '--coverage-openclover',
            'openclover.xml',
            '--coverage-php',
            'coverage.php',
            '--coverage-text=coverage.txt',
            '--coverage-xml',
            'coverage-xml',
            '--generate-baseline',
            'baseline.xml',
            '--list-tests-xml',
            'tests.xml',
        ]);

        try {
            $configuration = $this->configuration($cliConfiguration);

            $targets = FileOutputTargets::fromConfiguration($configuration, $cliConfiguration);
        } finally {
            rmdir($cacheDirectory);
        }

        $this->assertSame(
            [
                'cache directory'                     => $configuration->cacheDirectory(),
                'test run history'                    => $configuration->cacheDirectory() . DIRECTORY_SEPARATOR . 'test-run-history',
                'JUnit XML log'                       => 'junit.xml',
                'Open Test Reporting XML log'         => 'otr.xml',
                'TeamCity log'                        => 'teamcity.txt',
                'TestDox HTML log'                    => 'testdox.html',
                'TestDox text log'                    => 'testdox.txt',
                'event log'                           => $configuration->logEventsText(),
                'verbose event log'                   => $configuration->logEventsVerboseText(),
                'Clover XML code coverage report'     => 'clover.xml',
                'Cobertura XML code coverage report'  => 'cobertura.xml',
                'Crap4J XML code coverage report'     => 'crap4j.xml',
                'HTML code coverage report'           => 'coverage-html',
                'JSONL code coverage report'          => 'coverage.jsonl',
                'OpenClover XML code coverage report' => 'openclover.xml',
                'PHP code coverage report'            => 'coverage.php',
                'text code coverage report'           => 'coverage.txt',
                'XML code coverage report'            => 'coverage-xml',
                'baseline'                            => $configuration->generateBaseline(),
                'list of tests in XML format'         => 'tests.xml',
            ],
            $this->descriptionsToPaths($targets),
        );
    }

    /**
     * @param list<non-empty-string> $arguments
     */
    private function cliConfiguration(array $arguments): CliConfiguration
    {
        return new CliBuilder($this->createStub(Emitter::class))->fromParameters($arguments);
    }

    private function configuration(CliConfiguration $cliConfiguration): Configuration
    {
        return new Merger($this->createStub(Emitter::class))->merge(
            $cliConfiguration,
            DefaultConfiguration::create(),
        );
    }

    /**
     * @param list<FileOutputTarget> $targets
     *
     * @return array<non-empty-string, non-empty-string>
     */
    private function descriptionsToPaths(array $targets): array
    {
        $result = [];

        foreach ($targets as $target) {
            $result[$target->description()] = $target->path();
        }

        return $result;
    }
}
