<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use function sys_get_temp_dir;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Loader;

#[CoversClass(CodeCoverage::class)]
#[Small]
#[Group('test-runner')]
final class CodeCoverageTest extends TestCase
{
    public function testWarnsWhenNoFilterIsConfigured(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with('No filter is configured, code coverage will not be processed')
            ->seal();

        $configuration              = $this->configuration([]);
        $codeCoverageFilterRegistry = new CodeCoverageFilterRegistry;

        $codeCoverageFilterRegistry->init($configuration, true);

        $codeCoverage = new CodeCoverage($emitter);

        $codeCoverage->warnIfFilterIsNotConfigured($codeCoverageFilterRegistry, $configuration);

        $this->assertFalse($codeCoverage->isActive());
    }

    public function testWarnsWhenConfiguredFilterDoesNotMatchAnyFiles(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with('Configured source filter (include-path: ' . __DIR__ . '/does-not-exist) does not match any files, code coverage will not be processed')
            ->seal();

        $configuration              = $this->configuration(['--coverage-filter', __DIR__ . '/does-not-exist']);
        $codeCoverageFilterRegistry = new CodeCoverageFilterRegistry;

        $codeCoverageFilterRegistry->init($configuration, true);

        $codeCoverage = new CodeCoverage($emitter);

        $codeCoverage->warnIfFilterIsNotConfigured($codeCoverageFilterRegistry, $configuration);

        $this->assertFalse($codeCoverage->isActive());
    }

    public function testDoesNotWarnAboutFilesThatCouldNotBeParsedWhenCodeCoverageIsNotActive(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything())
            ->seal();

        $codeCoverage = new CodeCoverage($emitter);

        $codeCoverage->warnAboutFilesThatCouldNotBeParsed();

        $this->assertFalse($codeCoverage->isActive());
    }

    public function testProvidesTheCollectedCodeCoverageDataAndTheTestsItWasCollectedForWithoutTheFilter(): void
    {
        require_once __DIR__ . '/../../end-to-end/code-coverage/_files/code-coverage-driver/src/CustomDriverWithFakeData.php';

        $configuration = new Merger($this->createStub(Emitter::class))->merge(
            new Builder($this->createStub(Emitter::class))->fromParameters(
                ['--cache-directory', sys_get_temp_dir() . '/phpunit-collected-code-coverage-test'],
            ),
            new Loader($this->createStub(Emitter::class))->load(
                __DIR__ . '/../../end-to-end/code-coverage/_files/code-coverage-driver/phpunit-with-fake-data.xml',
            ),
        );

        $codeCoverage = new CodeCoverage($this->createStub(Emitter::class));

        $codeCoverage->init($configuration, new CodeCoverageFilterRegistry, false);

        $codeCoverage->codeCoverage()->setTests(['test' => ['size' => 'small', 'status' => 'success', 'time' => 0.0]]);

        $collected = $codeCoverage->collectedCodeCoverage();

        $this->assertSame($codeCoverage->codeCoverage()->getData(true), $collected->getData(true));
        $this->assertSame($codeCoverage->codeCoverage()->getTests(), $collected->getTests());
        $this->assertNotSame([], $codeCoverage->codeCoverage()->filter()->files());
        $this->assertSame([], $collected->filter()->files());
    }

    /**
     * @param list<string> $parameters
     */
    private function configuration(array $parameters): Configuration
    {
        return new Merger($this->createStub(Emitter::class))->merge(
            new Builder($this->createStub(Emitter::class))->fromParameters($parameters),
            DefaultConfiguration::create(),
        );
    }
}
